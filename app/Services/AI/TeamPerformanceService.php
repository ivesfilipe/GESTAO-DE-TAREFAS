<?php

namespace App\Services\AI;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TeamPerformanceService
{
    /**
     * Calcula métricas de performance de um liderado.
     */
    public function memberMetrics(User $member, ?Carbon $start = null, ?Carbon $end = null): array
    {
        $start ??= now()->subDays(30);
        $end ??= now();

        $tasks = Task::query()
            ->where('assigned_to', $member->id)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end])
                    ->orWhereBetween('completed_at', [$start, $end])
                    ->orWhereBetween('due_at', [$start, $end]);
            })
            ->get();

        $completed = $tasks->where('status', 'concluida');
        $totalCompleted = $completed->count();

        $cycleTimes = $completed
            ->filter(fn (Task $task) => $task->completed_at && $task->created_at)
            ->map(fn (Task $task) => $task->completed_at->diffInHours($task->created_at));

        $avgCycleHours = $cycleTimes->isEmpty() ? null : round($cycleTimes->avg(), 1);

        $overdue = $tasks->filter(fn (Task $task) => $task->isOverdue());
        $delivered = $tasks->whereIn('status', ['concluida', 'aguardando_aprovacao']);

        $overdueRate = $delivered->count() > 0
            ? round(($overdue->count() / $delivered->count()) * 100, 1)
            : 0.0;

        $rejected = $tasks->where('status', 'reprovada')->count();
        $rejectionRate = $delivered->count() > 0
            ? round(($rejected / $delivered->count()) * 100, 1)
            : 0.0;

        return [
            'member_id' => $member->id,
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'assigned_tasks' => $tasks->count(),
            'completed_tasks' => $totalCompleted,
            'overdue_tasks' => $overdue->count(),
            'overdue_rate' => $overdueRate,
            'rejected_tasks' => $rejected,
            'rejection_rate' => $rejectionRate,
            'avg_cycle_hours' => $avgCycleHours,
            'active_tasks' => $tasks->whereNotIn('status', ['concluida', 'cancelada'])->count(),
        ];
    }

    /**
     * Compara carga entre liderados ativos.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function workloadDistribution(): Collection
    {
        return User::where('role', 'liderado')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (User $member) => [
                'member_id' => $member->id,
                'name' => $member->name,
                'active_tasks' => Task::where('assigned_to', $member->id)
                    ->whereNotIn('status', ['concluida', 'cancelada'])
                    ->count(),
                'overdue_tasks' => Task::where('assigned_to', $member->id)
                    ->overdue()
                    ->count(),
            ]);
    }
}
