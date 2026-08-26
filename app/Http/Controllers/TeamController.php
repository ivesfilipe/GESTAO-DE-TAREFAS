<?php

namespace App\Http\Controllers;

use App\Actions\InviteUser;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TeamController extends Controller
{
    public function index()
    {
        Gate::authorize('manage-team');

        $liderados = User::where('role', 'liderado')->paginate();

        return view('team.index', compact('liderados'));
    }

    public function store(Request $request)
    {
        Gate::authorize('manage-team');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', 'in:liderado,gestor'],
        ]);

        $result = (new InviteUser)->execute(auth()->user(), $data);

        return redirect()->route('team.index')
            ->with('success', 'Convite criado.')
            ->with('invite_link', url('/convite/'.$result['token']));
    }

    /**
     * Gera novo link de definição de senha para um liderado existente
     * (link expirado após 48h ou esquecimento de senha).
     */
    public function regenerateInvite(User $user)
    {
        Gate::authorize('manage-team');

        $token = (new InviteUser)->createToken($user);

        return redirect()->route('team.index')
            ->with('success', "Novo link gerado para {$user->name}.")
            ->with('invite_link', url('/convite/'.$token));
    }

    /**
     * Exclui (soft delete) um liderado — apenas se ele não tiver
     * tarefas vinculadas (criadas por ele ou atribuídas a ele),
     * preservando o histórico e evitando relações quebradas.
     */
    public function destroy(User $user)
    {
        Gate::authorize('manage-team');

        if ($user->id === auth()->id()) {
            return redirect()->route('team.index')
                ->with('error', 'Você não pode excluir a si mesmo.');
        }

        $hasTasks = Task::where('assigned_to', $user->id)
            ->orWhere('created_by', $user->id)
            ->exists();

        if ($hasTasks) {
            return redirect()->route('team.index')
                ->with('error', "{$user->name} possui tarefas vinculadas. Desative-o em vez de excluir.");
        }

        $user->delete();

        return redirect()->route('team.index')
            ->with('success', "{$user->name} excluído(a) da equipe.");
    }

    public function toggleActive(User $user)
    {
        Gate::authorize('manage-team');

        $user->update(['is_active' => ! $user->is_active]);

        if (! $user->is_active) {
            $openTasksCount = Task::where('assigned_to', $user->id)
                ->whereNotIn('status', ['concluida', 'cancelada'])
                ->count();

            return redirect()->back()
                ->with('success', 'Usuário desativado.')
                ->with('open_tasks_count', $openTasksCount);
        }

        return redirect()->back()->with('success', 'Usuário reativado.');
    }
}
