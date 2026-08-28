<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('manage-team');
        $gestor = $request->user();

        $days = in_array($request->integer('periodo'), [30, 90, 365], true)
            ? $request->integer('periodo')
            : 30;

        $since = now()->subDays($days);

        $concluded = Task::query()
            ->forManager($gestor)
            ->where('status', 'concluida')
            ->whereBetween('completed_at', [$since, now()]);

        $avgCycleDays = (clone $concluded)
            ->whereNotNull('due_at')
            ->get(['created_at', 'completed_at'])
            ->map(fn (Task $t) => abs($t->created_at->diffInHours($t->completed_at)))
            ->avg();

        $avgCycleDays = $avgCycleDays !== null ? $avgCycleDays / 24 : null;

        $approvedCount = (clone $concluded)->count();

        $rejectedCount = Task::query()
            ->forManager($gestor)
            ->where('status', 'reprovada')
            ->whereBetween('updated_at', [$since, now()])
            ->count();

        $rejectionRate = ($approvedCount + $rejectedCount) > 0
            ? $rejectedCount / ($approvedCount + $rejectedCount) * 100
            : 0.0;

        $lateCount = (clone $concluded)
            ->whereNotNull('due_at')
            ->whereColumn('completed_at', '>', 'due_at')
            ->count();

        $lateRate = $approvedCount > 0 ? $lateCount / $approvedCount * 100 : 0.0;

        $createdCount = Task::withTrashed()
            ->forManager($gestor)
            ->whereBetween('created_at', [$since, now()])
            ->count();

        $rejectionCategories = Task::query()
            ->forManager($gestor)
            ->where('status', 'reprovada')
            ->whereBetween('updated_at', [$since, now()])
            ->selectRaw('rejection_category, COUNT(*) as total')
            ->groupBy('rejection_category')
            ->pluck('total', 'rejection_category');

        $categoryLabels = [
            'nao_atende' => 'Não atende aos requisitos',
            'escopo_mudou' => 'Escopo mudou',
            'info_incompleta' => 'Informações incompletas',
            'outro' => 'Outro',
        ];

        $perUser = User::query()
            ->where('role', 'liderado')
            ->managedBy($gestor)
            ->where('is_active', true)
            ->get()
            ->map(function (User $liderado) use ($since) {
                $concludedQuery = Task::query()
                    ->where('status', 'concluida')
                    ->where('assigned_to', $liderado->id)
                    ->whereBetween('completed_at', [$since, now()]);

                $concludedCount = (clone $concludedQuery)->count();

                $avgCycle = (clone $concludedQuery)
                    ->whereNotNull('due_at')
                    ->get(['created_at', 'completed_at'])
                    ->map(fn (Task $t) => abs($t->created_at->diffInHours($t->completed_at)))
                    ->avg();

                $avgCycle = $avgCycle !== null ? $avgCycle / 24 : null;

                $late = (clone $concludedQuery)
                    ->whereNotNull('due_at')
                    ->whereColumn('completed_at', '>', 'due_at')
                    ->count();

                $rejected = Task::query()
                    ->where('status', 'reprovada')
                    ->where('assigned_to', $liderado->id)
                    ->whereBetween('updated_at', [$since, now()])
                    ->count();

                $open = Task::query()
                    ->where('assigned_to', $liderado->id)
                    ->whereNotIn('status', ['concluida', 'cancelada'])
                    ->count();

                $overdue = Task::query()
                    ->where('assigned_to', $liderado->id)
                    ->get()
                    ->filter(fn (Task $t) => $t->isOverdue())
                    ->count();

                return (object) [
                    'user' => $liderado,
                    'concluded' => $concludedCount,
                    'avg_cycle_days' => $avgCycle !== null ? round((float) $avgCycle, 1) : null,
                    'late_rate' => $concludedCount > 0 ? round($late / $concludedCount * 100) : 0,
                    'rejected' => $rejected,
                    'open' => $open,
                    'overdue' => $overdue,
                ];
            })
            ->sortByDesc('concluded')
            ->values();

        $maxConcluded = max(1, $perUser->max('concluded'));

        return view('reports.index', compact(
            'days',
            'createdCount',
            'approvedCount',
            'rejectedCount',
            'rejectionRate',
            'avgCycleDays',
            'lateRate',
            'rejectionCategories',
            'categoryLabels',
            'perUser',
            'maxConcluded'
        ));
    }
}
