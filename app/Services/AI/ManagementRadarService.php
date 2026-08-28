<?php

namespace App\Services\AI;

use App\Models\Task;
use App\Models\User;
use App\Services\AI\Prompts\ManagementPrompts;

class ManagementRadarService
{
    private AIService $ai;

    private TeamPerformanceService $performance;

    public function __construct(?AIService $ai = null, ?TeamPerformanceService $performance = null)
    {
        $this->ai = $ai ?? app(AIService::class);
        $this->performance = $performance ?? new TeamPerformanceService;
    }

    /**
     * Retorna resumo de risco do time para o gestor.
     *
     * @return array<string, mixed>
     */
    public function radar(User $gestor): array
    {
        $tasks = Task::query()->forManager($gestor);
        $overdue = (clone $tasks)->overdue()->count();
        $blocked = (clone $tasks)->where('status', 'bloqueada')->count();
        $awaitingApproval = (clone $tasks)->where('status', 'aguardando_aprovacao')->count();
        $noAssignee = (clone $tasks)->where('status', 'nao_atribuida')->count();
        $urgent = (clone $tasks)->whereIn('priority', ['urgente', 'critica'])
            ->whereNotIn('status', ['concluida', 'cancelada'])
            ->count();

        $workload = $this->performance->workloadDistribution($gestor);

        // Resumo determinístico (não depende de LLM)
        $deterministicSummary = $this->buildDeterministicSummary($overdue, $blocked, $awaitingApproval, $noAssignee, $urgent, $workload);

        // Resumo LLM (opcional, como complemento)
        $context = $this->buildRadarContext($overdue, $blocked, $awaitingApproval, $noAssignee, $urgent, $workload);

        $response = $this->ai->ask(
            system: ManagementPrompts::radarSummary(),
            user: $context,
            temperature: 0.4,
            maxTokens: 300,
            entities: $this->entitiesForContext($gestor),
        );

        return [
            'metrics' => [
                'overdue' => $overdue,
                'blocked' => $blocked,
                'awaiting_approval' => $awaitingApproval,
                'no_assignee' => $noAssignee,
                'urgent' => $urgent,
            ],
            'workload' => $workload,
            'summary' => $response->content,
            'deterministic_summary' => $deterministicSummary,
            'ai_provider' => $this->ai->provider()->name(),
            'ai_mock' => $this->ai->isMock(),
        ];
    }

    /**
     * Resumo determinístico (não usa LLM) - para radar sem IA externa.
     */
    public function deterministicRadar(User $gestor): array
    {
        $tasks = Task::query()->forManager($gestor);
        $overdue = (clone $tasks)->overdue()->count();
        $blocked = (clone $tasks)->where('status', 'bloqueada')->count();
        $awaitingApproval = (clone $tasks)->where('status', 'aguardando_aprovacao')->count();
        $noAssignee = (clone $tasks)->where('status', 'nao_atribuida')->count();
        $urgent = (clone $tasks)->whereIn('priority', ['urgente', 'critica'])
            ->whereNotIn('status', ['concluida', 'cancelada'])
            ->count();

        $workload = $this->performance->workloadDistribution($gestor);

        return [
            'metrics' => [
                'overdue' => $overdue,
                'blocked' => $blocked,
                'awaiting_approval' => $awaitingApproval,
                'no_assignee' => $noAssignee,
                'urgent' => $urgent,
            ],
            'workload' => $workload,
            'summary' => $this->buildDeterministicSummary($overdue, $blocked, $awaitingApproval, $noAssignee, $urgent, $workload),
            'ai_provider' => 'deterministic',
            'ai_mock' => true,
        ];
    }

    /**
     * Top 5 ações prioritárias baseadas em risco/impacto (determinístico).
     *
     * @return list<array<string, mixed>>
     */
    public function topPriorities(User $gestor, int $limit = 5): array
    {
        $tasks = Task::query()->forManager($gestor)
            ->whereNotIn('status', ['concluida', 'cancelada'])
            ->with('assignee')
            ->get();

        $priorities = $tasks->map(function (Task $task) {
            $score = 0;
            $reasons = [];

            if ($task->isOverdue()) {
                $score += 50;
                $reasons[] = 'Atrasada';
            } elseif ($task->due_at && $task->due_at->isToday()) {
                $score += 30;
                $reasons[] = 'Vence hoje';
            } elseif ($task->due_at && $task->due_at->isTomorrow()) {
                $score += 15;
                $reasons[] = 'Vence amanhã';
            }

            if ($task->status === 'bloqueada') {
                $score += 40;
                $reasons[] = 'Bloqueada';
            }
            if ($task->status === 'aguardando_aprovacao') {
                $score += 25;
                $reasons[] = 'Aguardando aprovação';
            }

            $priorityWeight = match ($task->priority) {
                'critica' => 20,
                'urgente' => 15,
                'importante' => 10,
                default => 5,
            };
            $score += $priorityWeight;

            if ($task->priority === 'critica') {
                $reasons[] = 'Crítica';
            } elseif ($task->priority === 'urgente') {
                $reasons[] = 'Urgente';
            }

            return [
                'task_id' => $task->id,
                'title' => $task->title,
                'score' => $score,
                'reasons' => $reasons,
                'priority' => $task->priority,
                'status' => $task->status,
                'due_at' => $task->due_at?->format('d/m/Y H:i'),
                'assignee' => $task->assignee?->name,
            ];
        })
            ->filter(fn ($item) => $item['score'] > 0)
            ->sortByDesc('score')
            ->take($limit)
            ->values()
            ->all();

        return $priorities;
    }

    private function buildRadarContext(int $overdue, int $blocked, int $awaitingApproval, int $noAssignee, int $urgent, $workload): string
    {
        $lines = [
            'Resumo das tarefas do time:',
            "- Atrasadas: {$overdue}",
            "- Bloqueadas: {$blocked}",
            "- Aguardando aprovação: {$awaitingApproval}",
            "- Sem responsável: {$noAssignee}",
            "- Urgentes/críticas ativas: {$urgent}",
            '',
            'Distribuição de carga (tarefas ativas por pessoa):',
        ];

        foreach ($workload as $member) {
            $lines[] = "- [PESSOA_ANONIMA_{$member['member_id']}]: {$member['active_tasks']} ativas, {$member['overdue_tasks']} atrasadas";
        }

        return implode("\n", $lines);
    }

    private function buildDeterministicSummary(int $overdue, int $blocked, int $awaitingApproval, int $noAssignee, int $urgent, $workload): string
    {
        $parts = [];

        if ($overdue > 0) {
            $parts[] = "{$overdue} tarefa(s) atrasada(s) exigem ação imediata.";
        }
        if ($blocked > 0) {
            $parts[] = "{$blocked} tarefa(s) bloqueada(s) precisam de desbloqueio.";
        }
        if ($awaitingApproval > 0) {
            $parts[] = "{$awaitingApproval} tarefa(s) aguardando sua aprovação.";
        }
        if ($noAssignee > 0) {
            $parts[] = "{$noAssignee} tarefa(s) sem responsável definido.";
        }
        if ($urgent > 0) {
            $parts[] = "{$urgent} tarefa(s) urgente/crítica ativa(s).";
        }

        $overloaded = $workload->filter(fn ($m) => $m['active_tasks'] > 5 && $m['overdue_tasks'] > 0)->count();
        if ($overloaded > 0) {
            $parts[] = "{$overloaded} liderado(s) com sobrecarga e atrasos.";
        }

        if (empty($parts)) {
            return 'Nenhum risco crítico identificado no momento.';
        }

        return implode(' ', $parts);
    }

    private function entitiesForContext(User $gestor): array
    {
        $entities = [];
        $liderados = User::where('role', 'liderado')->managedBy($gestor)->where('is_active', true)->get();

        foreach ($liderados as $liderado) {
            $entities = array_merge($entities, app(Safety\ZeroDataRetention::class)->entitiesFromUser($liderado));
        }

        return $entities;
    }
}
