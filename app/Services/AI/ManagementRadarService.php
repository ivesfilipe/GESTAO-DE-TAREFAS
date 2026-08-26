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
        $overdue = Task::overdue()->count();
        $blocked = Task::where('status', 'bloqueada')->count();
        $awaitingApproval = Task::where('status', 'aguardando_aprovacao')->count();
        $noAssignee = Task::where('status', 'nao_atribuida')->count();
        $urgent = Task::whereIn('priority', ['urgente', 'critica'])
            ->whereNotIn('status', ['concluida', 'cancelada'])
            ->count();

        $workload = $this->performance->workloadDistribution();

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
            'ai_provider' => $this->ai->provider()->name(),
            'ai_mock' => $this->ai->isMock(),
        ];
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
