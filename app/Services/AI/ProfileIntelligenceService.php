<?php

namespace App\Services\AI;

use App\Models\TeamMemberProfile;
use App\Models\User;
use App\Services\AI\Prompts\ManagementPrompts;
use App\Services\AI\Safety\ZeroDataRetention;
use App\Services\AI\Tools\AITools;
use Illuminate\Support\Collection;

class ProfileIntelligenceService
{
    private AIService $ai;

    private TeamPerformanceService $performance;

    private TeamKnowledgeService $knowledge;

    private ZeroDataRetention $zdr;

    public function __construct(
        ?AIService $ai = null,
        ?TeamPerformanceService $performance = null,
        ?TeamKnowledgeService $knowledge = null,
        ?ZeroDataRetention $zdr = null,
    ) {
        $this->ai = $ai ?? app(AIService::class);
        $this->performance = $performance ?? new TeamPerformanceService;
        $this->knowledge = $knowledge ?? new TeamKnowledgeService;
        $this->zdr = $zdr ?? new ZeroDataRetention;
    }

    /**
     * Gera ou atualiza o resumo inteligente do perfil do liderado.
     */
    public function updateIntelligence(User $member): TeamMemberProfile
    {
        $profile = TeamMemberProfile::firstOrNew(['user_id' => $member->id]);
        $metrics = $this->performance->memberMetrics($member);
        $chunks = $this->knowledge->retrieve($member, 'responsabilidades objetivos função competências', 3);

        $context = $this->buildContext($member, $profile, $metrics, $chunks);
        $entities = $this->zdr->entitiesFromUser($member);

        $response = $this->ai->ask(
            system: ManagementPrompts::teamMemberProfile(),
            user: $context,
            temperature: 0.4,
            maxTokens: 800,
            entities: $entities,
            responseFormat: ['type' => 'json_object'],
        );

        $parsed = AITools::extractJson($response->content);

        $profile->summary = $parsed['summary'] ?? 'Resumo não gerado.';
        $profile->strengths = AITools::normalizeItems($parsed['strengths'] ?? [], 5);
        $profile->gaps = AITools::normalizeItems($parsed['gaps'] ?? [], 4);
        $profile->preferences = AITools::normalizeItems($parsed['preferences'] ?? [], 4);
        $profile->ai_summary_sources = $chunks
            ->map(fn ($chunk) => $chunk->document?->name)
            ->filter()
            ->unique()
            ->values()
            ->all();
        $profile->generated_at = now();
        $profile->summary_invalidated_at = null;
        $profile->save();

        return $profile;
    }

    /**
     * @param  array<string, mixed>  $metrics
     */
    private function buildContext(User $member, TeamMemberProfile $profile, array $metrics, Collection $chunks): string
    {
        $lines = [
            'Perfil profissional:',
            '- Função: '.($profile->role ?? 'não registrada'),
            '- Setor: '.($profile->department ?? 'não registrado'),
            '- Resumo da função: '.($profile->function_summary ?? 'não registrado'),
            '- Responsabilidades: '.($profile->responsibilities ? implode(', ', $profile->responsibilities) : 'não registradas'),
            '- Responsabilidades recorrentes: '.($profile->recurring_responsibilities ? implode(', ', $profile->recurring_responsibilities) : 'não registradas'),
            '- Objetivos profissionais: '.($profile->professional_objectives ? implode(', ', $profile->professional_objectives) : 'não registrados'),
            '- Orientações de delegação: '.($profile->delegation_guidelines ?? 'não registradas'),
            '',
            'Métricas operacionais (últimos 30 dias):',
            '- Tarefas atribuídas: '.$metrics['assigned_tasks'],
            '- Concluídas: '.$metrics['completed_tasks'],
            '- Atrasadas: '.$metrics['overdue_tasks'],
            '- Reprovadas: '.$metrics['rejected_tasks'],
            '- Tempo médio de ciclo: '.($metrics['avg_cycle_hours'] ? $metrics['avg_cycle_hours'].'h' : 'n/a'),
        ];

        if ($chunks->isNotEmpty()) {
            $lines[] = '';
            $lines[] = 'Documentos relevantes:';
            foreach ($chunks as $chunk) {
                $lines[] = '- '.($chunk->document?->name ?? 'Documento').': '.mb_substr($chunk->content, 0, 200);
            }
        }

        return implode("\n", $lines);
    }
}
