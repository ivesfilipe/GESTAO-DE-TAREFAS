<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function index()
    {
        Gate::authorize('manage-team');
        $gestor = auth()->user();
        $tasks = Task::query()->forManager($gestor);

        $tarefasAtrasadas = (clone $tasks)->whereNotIn('status', ['concluida', 'cancelada'])
            ->where('due_at', '<', now())
            ->where('status', '!=', 'bloqueada')
            ->count();

        $tarefasUrgentes = (clone $tasks)->whereIn('priority', ['urgente', 'critica'])
            ->whereNotIn('status', ['concluida', 'cancelada'])
            ->count();

        $vencemHoje = (clone $tasks)->whereDate('due_at', now()->toDateString())->count();

        $aguardandoAprovacao = (clone $tasks)->where('status', 'aguardando_aprovacao')->count();

        $liderados = User::where('role', 'liderado')->managedBy($gestor)->get();
        $visaoPorPessoa = [];

        foreach ($liderados as $liderado) {
            $visaoPorPessoa[] = [
                'user' => $liderado,
                'abertas' => Task::where('assigned_to', $liderado->id)
                    ->whereNotIn('status', ['concluida', 'cancelada'])
                    ->count(),
                'atrasadas' => Task::where('assigned_to', $liderado->id)
                    ->whereNotIn('status', ['concluida', 'cancelada'])
                    ->where('due_at', '<', now())
                    ->where('status', '!=', 'bloqueada')
                    ->count(),
            ];
        }

        return view('dashboard.index', compact(
            'tarefasAtrasadas',
            'tarefasUrgentes',
            'vencemHoje',
            'aguardandoAprovacao',
            'visaoPorPessoa'
        ));
    }
}
