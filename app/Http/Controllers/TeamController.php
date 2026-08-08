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

        $result = (new InviteUser())->execute(auth()->user(), $data);

        return redirect()->route('team.index')
            ->with('success', 'Convite enviado.')
            ->with('invite_token', $result['token']);
    }

    public function toggleActive(User $user)
    {
        Gate::authorize('manage-team');

        $user->update(['is_active' => !$user->is_active]);

        if (!$user->is_active) {
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
