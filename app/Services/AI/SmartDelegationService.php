<?php

namespace App\Services\AI;

use App\Models\User;
use App\Services\AI\Prompts\ManagementPrompts;
use App\Services\AI\Safety\ZeroDataRetention;
use App\Services\AI\Tools\AITools;
use App\Services\NaturalLanguageTaskParser;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class SmartDelegationService
{
    private AIService $ai;

    private TeamPerformanceService $performance;

    private TeamKnowledgeService $knowledge;

    private NaturalLanguageTaskParser $parser;

    private ZeroDataRetention $zdr;

    public function __construct(
        ?AIService $ai = null,
        ?TeamPerformanceService $performance = null,
        ?TeamKnowledgeService $knowledge = null,
        ?NaturalLanguageTaskParser $parser = null,
        ?ZeroDataRetention $zdr = null,
    ) {
        $this->ai = $ai ?? app(AIService::class);
        $this->performance = $performance ?? new TeamPerformanceService;
        $this->knowledge = $knowledge ?? new TeamKnowledgeService;
        $this->parser = $parser ?? new NaturalLanguageTaskParser;
        $this->zdr = $zdr ?? new ZeroDataRetention;
    }

    /**
     * Gera um rascunho estruturado de tarefa a partir de texto livre.
     *
     * @return array<string, mixed>
     */
    public function draft(User $gestor, string $input, ?User $selectedAssignee = null): array
    {
        $parsed = $this->parseInput($input);
        $candidates = $this->candidateAssignees($gestor);

        $context = $this->buildContext($gestor, $input, $parsed, $candidates, $selectedAssignee);
        $entities = $this->entitiesForContext($candidates, $selectedAssignee);

        $response = $this->ai->ask(
            system: ManagementPrompts::smartDelegation(),
            user: $context,
            temperature: 0.3,
            maxTokens: 1200,
            entities: $entities,
            responseFormat: ['type' => 'json_object'],
        );

        $parsedResponse = AITools::extractJson($response->content);

        if (! is_array($parsedResponse)) {
            throw new InvalidArgumentException('IA retornou resposta inválida.');
        }

        return $this->normalizeDraft($parsedResponse, $parsed, $candidates, $selectedAssignee);
    }

    /**
     * @return array<string, mixed>
     */
    private function parseInput(string $input): array
    {
        try {
            return (new NaturalLanguageTaskParser)->parse($input);
        } catch (\Throwable $e) {
            return [
                'title' => '',
                'due_at' => null,
                'priority' => 'normal',
                'recurrence_frequency' => null,
            ];
        }
    }

    /**
     * @return Collection<int, User>
     */
    private function candidateAssignees(User $gestor): Collection
    {
        return User::where('role', 'liderado')
            ->managedBy($gestor)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $parsed
     * @param  Collection<int, User>  $candidates
     */
    private function buildContext(User $gestor, string $input, array $parsed, Collection $candidates, ?User $selectedAssignee): string
    {
        $lines = [
            'Texto do gestor:',
            $input,
            '',
            'Dados já interpretados pelo parser:',
            '- Título sugerido: '.($parsed['title'] ?: 'não identificado'),
            '- Prioridade: '.($parsed['priority'] ?? 'normal'),
            '- Prazo sugerido: '.($parsed['due_at'] ? $parsed['due_at']->format('d/m/Y H:i') : 'não identificado'),
            '- Recorrência: '.($parsed['recurrence_frequency'] ?? 'não identificada'),
            '',
        ];

        if ($selectedAssignee) {
            $lines[] = 'Responsável selecionado pelo gestor: [PESSOA_ANONIMA_'.$selectedAssignee->id.']';
            $lines[] = $this->profileContext($selectedAssignee);
        } else {
            $lines[] = 'Nenhum responsável selecionado. Candidatos disponíveis (ordem de carga):';
            foreach ($this->performance->workloadDistribution($gestor) as $member) {
                $lines[] = "- [PESSOA_ANONIMA_{$member['member_id']}]: {$member['active_tasks']} ativas, {$member['overdue_tasks']} atrasadas";
            }
        }

        $lines[] = '';
        $lines[] = 'Responda APENAS com o JSON no formato exigido.';

        return implode("\n", $lines);
    }

    private function profileContext(User $member): string
    {
        $profile = $member->teamProfile;
        $metrics = $this->performance->memberMetrics($member);

        $lines = [
            'Perfil profissional:',
            '- Função: '.($profile?->role ?? 'não registrada'),
            '- Setor: '.($profile?->department ?? 'não registrado'),
            '- Resumo: '.($profile?->function_summary ?? 'não registrado'),
            '- Responsabilidades: '.($profile?->responsibilities ? implode(', ', $profile->responsibilities) : 'não registradas'),
            '- Objetivos profissionais: '.($profile?->professional_objectives ? implode(', ', $profile->professional_objectives) : 'não registrados'),
            '- Orientações de delegação: '.($profile?->delegation_guidelines ?? 'não registradas'),
            '- Tarefas ativas: '.$metrics['active_tasks'],
            '- Atrasadas: '.$metrics['overdue_tasks'],
        ];

        $documents = $this->knowledge->retrieve($member, 'responsabilidades objetivos função', 3);
        if ($documents->isNotEmpty()) {
            $lines[] = '- Documentos relevantes:';
            foreach ($documents as $chunk) {
                $lines[] = '  * '.mb_substr($chunk->content, 0, 200);
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @param  Collection<int, User>  $candidates
     * @return array<string, string>
     */
    private function entitiesForContext(Collection $candidates, ?User $selectedAssignee): array
    {
        $entities = [];

        foreach ($candidates as $candidate) {
            $entities = array_merge($entities, $this->zdr->entitiesFromUser($candidate));
        }

        if ($selectedAssignee) {
            $entities = array_merge($entities, $this->zdr->entitiesFromUser($selectedAssignee));
        }

        return $entities;
    }

    /**
     * @param  array<string, mixed>  $response
     * @param  array<string, mixed>  $parsed
     * @param  Collection<int, User>  $candidates
     * @return array<string, mixed>
     */
    private function normalizeDraft(array $response, array $parsed, Collection $candidates, ?User $selectedAssignee): array
    {
        $recommendedId = $response['recommended_assignee_id'] ?? null;
        $recommended = $this->resolveRecommendedAssignee($recommendedId, $candidates, $selectedAssignee);

        $dueAt = $parsed['due_at'];
        if (! $dueAt && ! empty($response['due_at_suggestion'])) {
            if (is_string($response['due_at_suggestion']) && preg_match('/^\d{4}-\d{2}-\d{2}/', $response['due_at_suggestion'])) {
                $dueAt = CarbonImmutable::parse($response['due_at_suggestion']);
            } elseif (is_numeric($response['due_at_suggestion'])) {
                $dueAt = CarbonImmutable::now()->addDays((int) $response['due_at_suggestion'])->setTime(17, 0);
            }
        }
        if (! $dueAt) {
            $dueAt = CarbonImmutable::now()->addDay()->setTime(17, 0);
        }

        $priority = $parsed['priority'] ?? 'normal';
        if (in_array($response['priority'] ?? '', ['normal', 'importante', 'urgente', 'critica'])) {
            $priority = $response['priority'];
        }

        $title = $parsed['title'] ?: ($response['title'] ?? '');

        return [
            'title' => $title,
            'task_type' => in_array($response['task_type'] ?? '', ['demanda', 'compra', 'servico', 'desenvolvimento', 'responsabilidade', 'outro'])
                ? $response['task_type']
                : 'demanda',
            'priority' => $priority,
            'due_at' => $dueAt->format('Y-m-d\TH:i'),
            'due_at_label' => $dueAt->format('d/m/Y H:i'),
            'due_at_reason' => $response['due_at_reason'] ?? null,
            'recommended_assignee_id' => $recommended?->id,
            'recommended_assignee_name' => $recommended?->name,
            'assignee_reason' => $response['assignee_reason'] ?? null,
            'description' => $response['description'] ?? '',
            'acceptance_criteria' => AITools::normalizeItems($response['acceptance_criteria'] ?? [], 5),
            'expected_evidence' => AITools::normalizeItems($response['expected_evidence'] ?? [], 3),
            'checkpoints' => AITools::normalizeItems($response['checkpoints'] ?? [], 5),
            'missing_information' => AITools::normalizeItems($response['missing_information'] ?? [], 5),
            'confidence' => in_array($response['confidence'] ?? '', ['alta', 'media', 'baixa'])
                ? $response['confidence']
                : 'media',
            'recurrence_frequency' => $parsed['recurrence_frequency'],
            'ai_provider' => $this->ai->provider()->name(),
            'ai_mock' => $this->ai->isMock(),
        ];
    }

    /**
     * @param  Collection<int, User>  $candidates
     */
    private function resolveRecommendedAssignee(mixed $recommendedId, Collection $candidates, ?User $selectedAssignee): ?User
    {
        if ($selectedAssignee) {
            return $selectedAssignee;
        }

        if (! is_numeric($recommendedId)) {
            return $candidates->first();
        }

        $candidate = $candidates->firstWhere('id', (int) $recommendedId);

        // Descartar ID inválido (não enviado como candidato)
        if (! $candidate) {
            return $candidates->first();
        }

        return $candidate;
    }
}
