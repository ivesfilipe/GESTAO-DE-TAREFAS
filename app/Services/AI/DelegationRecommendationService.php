<?php

namespace App\Services\AI;

use App\Models\User;
use App\Services\AI\Prompts\ManagementPrompts;
use App\Services\AI\Tools\AITools;
use Illuminate\Support\Collection;

class DelegationRecommendationService
{
    private AIService $ai;

    private TeamPerformanceService $performance;

    public function __construct(?AIService $ai = null, ?TeamPerformanceService $performance = null)
    {
        $this->ai = $ai ?? app(AIService::class);
        $this->performance = $performance ?? new TeamPerformanceService;
    }

    /**
     * Recomenda assignee, tipo, critérios, evidências e prazo para uma tarefa.
     *
     * @return array<string, mixed>
     */
    public function recommend(User $gestor, string $title, ?string $description = null, ?string $priority = null): array
    {
        $workload = $this->performance->workloadDistribution();
        $suggestedAssignee = $this->suggestAssignee($workload);

        $context = $this->buildPromptContext($title, $description, $priority, $workload);

        $response = $this->ai->ask(
            system: ManagementPrompts::delegationSuggestion(),
            user: $context,
            temperature: 0.4,
            maxTokens: 600,
            entities: $this->entitiesForContext($gestor),
        );

        $parsed = AITools::extractJson($response->content);

        return [
            'suggested_assignee_id' => $suggestedAssignee?->id,
            'suggested_assignee_name' => $suggestedAssignee?->name,
            'task_type' => $parsed['task_type'] ?? 'demanda',
            'acceptance_criteria' => AITools::normalizeItems($parsed['acceptance_criteria'] ?? [], 5),
            'expected_evidence' => AITools::normalizeItems($parsed['expected_evidence'] ?? [], 3),
            'suggested_due_in_days' => is_numeric($parsed['suggested_due_in_days'] ?? null)
                ? (int) $parsed['suggested_due_in_days']
                : 3,
            'reasoning' => $parsed['reasoning'] ?? 'Sugestão baseada no contexto da tarefa.',
            'ai_provider' => $this->ai->provider()->name(),
            'ai_mock' => $this->ai->isMock(),
        ];
    }

    private function suggestAssignee(Collection $workload): ?User
    {
        $candidate = $workload
            ->sortBy('active_tasks')
            ->first();

        if (! $candidate) {
            return null;
        }

        return User::find($candidate['member_id']);
    }

    private function buildPromptContext(string $title, ?string $description, ?string $priority, Collection $workload): string
    {
        $lines = [
            'Tarefa: '.$title,
        ];

        if ($description) {
            $lines[] = 'Descrição: '.$description;
        }

        if ($priority) {
            $lines[] = 'Prioridade: '.$priority;
        }

        $lines[] = 'Distribuição de carga atual dos liderados (tarefas ativas):';

        foreach ($workload as $member) {
            $lines[] = "- [PESSOA_ANONIMA_{$member['member_id']}]: {$member['active_tasks']} ativas, {$member['overdue_tasks']} atrasadas";
        }

        return implode("\n", $lines);
    }

    private function entitiesForContext(User $gestor): array
    {
        $entities = [];
        $liderados = User::where('role', 'liderado')->where('is_active', true)->get();

        foreach ($liderados as $liderado) {
            $entities = array_merge($entities, app(Safety\ZeroDataRetention::class)->entitiesFromUser($liderado));
        }

        return $entities;
    }
}
