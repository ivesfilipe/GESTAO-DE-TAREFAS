<?php

namespace App\Services\AI;

use App\Models\TeamMemberProfile;
use App\Models\User;
use App\Services\AI\Prompts\ManagementPrompts;
use App\Services\AI\Safety\ZeroDataRetention;
use App\Services\AI\Tools\AITools;

class TaskSuggestionService
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
     * @return list<array<string, mixed>>
     */
    public function suggest(User $member, ?string $category = null): array
    {
        $profile = TeamMemberProfile::firstOrNew(['user_id' => $member->id]);
        $metrics = $this->performance->memberMetrics($member);

        $context = $this->buildContext($member, $profile, $metrics, $category);
        $entities = $this->zdr->entitiesFromUser($member);

        $response = $this->ai->ask(
            system: ManagementPrompts::taskSuggestions(),
            user: $context,
            temperature: 0.4,
            maxTokens: 1200,
            entities: $entities,
            responseFormat: ['type' => 'json_object'],
        );

        $parsed = AITools::extractJson($response->content);

        if (! is_array($parsed) || ! isset($parsed['suggestions']) || ! is_array($parsed['suggestions'])) {
            return [];
        }

        return collect($parsed['suggestions'])
            ->filter(fn ($item) => is_array($item) && ! empty($item['title']))
            ->map(fn (array $item) => [
                'category' => $item['category'] ?? $category ?? 'demanda',
                'title' => $item['title'],
                'task_type' => in_array($item['task_type'] ?? '', ['demanda', 'compra', 'servico', 'desenvolvimento', 'responsabilidade', 'outro'])
                    ? $item['task_type']
                    : 'demanda',
                'objective' => $item['objective'] ?? '',
                'reason' => $item['reason'] ?? $item['justification'] ?? '',
                'periodicity' => $item['periodicity'] ?? null,
                'priority' => in_array($item['priority'] ?? '', ['normal', 'importante', 'urgente', 'critica'])
                    ? $item['priority']
                    : 'normal',
            ])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $metrics
     */
    private function buildContext(User $member, TeamMemberProfile $profile, array $metrics, ?string $category): string
    {
        $lines = [
            'Categoria solicitada: '.($category ?? 'todas'),
            '',
            'Perfil profissional:',
            '- Função: '.($profile->role ?? 'não registrada'),
            '- Setor: '.($profile->department ?? 'não registrado'),
            '- Responsabilidades: '.($profile->responsibilities ? implode(', ', $profile->responsibilities) : 'não registradas'),
            '- Responsabilidades recorrentes: '.($profile->recurring_responsibilities ? implode(', ', $profile->recurring_responsibilities) : 'não registradas'),
            '- Objetivos profissionais: '.($profile->professional_objectives ? implode(', ', $profile->professional_objectives) : 'não registrados'),
            '',
            'Métricas operacionais (últimos 30 dias):',
            '- Tarefas atribuídas: '.$metrics['assigned_tasks'],
            '- Concluídas: '.$metrics['completed_tasks'],
            '- Atrasadas: '.$metrics['overdue_tasks'],
            '- Reprovadas: '.$metrics['rejected_tasks'],
        ];

        $chunks = $this->knowledge->retrieve($member, 'responsabilidades objetivos função', 3);
        if ($chunks->isNotEmpty()) {
            $lines[] = '';
            $lines[] = 'Documentos relevantes:';
            foreach ($chunks as $chunk) {
                $lines[] = '- '.($chunk->document?->name ?? 'Documento').': '.mb_substr($chunk->content, 0, 200);
            }
        }

        $lines[] = '';
        $lines[] = 'Responda APENAS com o objeto JSON no formato: {"suggestions": [{"category", "title", "task_type", "objective", "reason", "periodicity", "priority"}]}';

        return implode("\n", $lines);
    }
}
