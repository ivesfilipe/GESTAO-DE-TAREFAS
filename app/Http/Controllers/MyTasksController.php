<?php

namespace App\Http\Controllers;

use App\Models\Task;

class MyTasksController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $urgentes = Task::where('assigned_to', $user->id)
            ->whereIn('priority', ['urgente', 'critica'])
            ->whereNotIn('status', ['concluida', 'cancelada'])
            ->get();

        $hoje = Task::where('assigned_to', $user->id)
            ->whereDate('due_at', now()->toDateString())
            ->get();

        $proximas = Task::where('assigned_to', $user->id)
            ->whereNotIn('status', ['concluida', 'cancelada'])
            ->whereNotIn('id', $urgentes->pluck('id')->merge($hoje->pluck('id')))
            ->get();

        return view('my-tasks.index', compact('urgentes', 'hoje', 'proximas'));
    }
}
