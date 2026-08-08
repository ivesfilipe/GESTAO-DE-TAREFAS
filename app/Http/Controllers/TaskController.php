<?php

namespace App\Http\Controllers;

use App\Actions\ApproveTask;
use App\Actions\AssignTask;
use App\Actions\BlockTask;
use App\Actions\ChangeTaskStatus;
use App\Actions\CreateChangeRequest;
use App\Actions\CreateTask;
use App\Actions\RejectTask;
use App\Actions\ResolveChangeRequest;
use App\Actions\UnblockTask;
use App\Events\ConclusaoSolicitada;
use App\Models\ChangeRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->isGestor()) {
            $query = Task::query();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('assigned_to')) {
                $query->where('assigned_to', $request->assigned_to);
            }

            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            if ($request->filled('search')) {
                $query->where('title', 'like', '%' . $request->search . '%');
            }

            $tasks = $query->latest()->paginate();
        } else {
            $tasks = Task::where('assigned_to', $user->id)
                ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
                ->when($request->filled('priority'), fn($q) => $q->where('priority', $request->priority))
                ->when($request->filled('search'), fn($q) => $q->where('title', 'like', '%' . $request->search . '%'))
                ->latest()
                ->paginate();
        }

        return view('tasks.index', compact('tasks'));
    }

    public function create()
    {
        Gate::authorize('create-task');

        $liderados = User::where('role', 'liderado')->where('is_active', true)->get();

        return view('tasks.create', compact('liderados'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create-task');

        $data = $request->validate([
            'title' => ['required', 'max:255'],
            'description' => ['nullable'],
            'priority' => ['required', 'in:normal,importante,urgente,critica'],
            'due_at' => ['required', 'date', 'after:now'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        (new CreateTask())->execute(auth()->user(), $data);

        return redirect()->route('tasks.index')->with('success', 'Tarefa criada.');
    }

    public function show(Task $task)
    {
        Gate::authorize('view-task', $task);

        $task->load(['comments.author', 'attachments', 'historyEvents.actor', 'changeRequests']);

        return view('tasks.show', compact('task'));
    }

    public function assign(Request $request, Task $task)
    {
        Gate::authorize('create-task');

        $request->validate([
            'assigned_to' => ['required', 'exists:users,id'],
        ]);

        $newAssignee = User::find($request->assigned_to);

        (new AssignTask())->execute($task, auth()->user(), $newAssignee);

        return redirect()->back()->with('success', 'Responsável atualizado.');
    }

    public function changeStatus(Request $request, Task $task)
    {
        $request->validate([
            'status' => ['required', 'in:' . implode(',', Task::statuses())],
        ]);

        ChangeTaskStatus::change($task, auth()->user(), $request->status);

        return redirect()->back()->with('success', 'Status atualizado.');
    }

    public function block(Request $request, Task $task)
    {
        if (auth()->id() !== $task->assigned_to || !auth()->user()->isLiderado()) {
            abort(403);
        }

        $request->validate([
            'block_reason' => ['required'],
            'blocked_on' => ['required'],
        ]);

        (new BlockTask())->execute($task, auth()->user(), $request->block_reason, $request->blocked_on);

        return redirect()->back()->with('success', 'Tarefa bloqueada.');
    }

    public function unblock(Request $request, Task $task)
    {
        if (!auth()->user()->isGestor() && auth()->id() !== $task->assigned_to) {
            abort(403);
        }

        (new UnblockTask())->execute($task, auth()->user());

        return redirect()->back()->with('success', 'Tarefa desbloqueada.');
    }

    public function requestCompletion(Task $task)
    {
        if (auth()->id() !== $task->assigned_to || !auth()->user()->isLiderado()) {
            abort(403);
        }

        ChangeTaskStatus::change($task, auth()->user(), 'aguardando_aprovacao');

        ConclusaoSolicitada::dispatch($task, auth()->user());

        return redirect()->back()->with('success', 'Conclusão solicitada.');
    }

    public function approve(Task $task)
    {
        Gate::authorize('approve-task', $task);

        (new ApproveTask())->execute($task, auth()->user());

        return redirect()->back()->with('success', 'Tarefa aprovada.');
    }

    public function reject(Request $request, Task $task)
    {
        Gate::authorize('reject-task', $task);

        $request->validate([
            'rejection_category' => ['required', 'in:nao_atende,escopo_mudou,info_incompleta,outro'],
            'rejection_note' => ['required'],
        ]);

        (new RejectTask())->execute($task, auth()->user(), $request->rejection_category, $request->rejection_note);

        return redirect()->back()->with('success', 'Tarefa reprovada.');
    }

    public function requestChange(Request $request, Task $task)
    {
        if (auth()->id() !== $task->assigned_to || !auth()->user()->isLiderado()) {
            abort(403);
        }

        $request->validate([
            'field' => ['required', 'in:due_at,priority'],
            'current_value' => ['required'],
            'requested_value' => ['required'],
            'justification' => ['required'],
        ]);

        (new CreateChangeRequest())->execute(
            $task,
            auth()->user(),
            $request->field,
            $request->current_value,
            $request->requested_value,
            $request->justification
        );

        return redirect()->back()->with('success', 'Solicitação de alteração enviada.');
    }

    public function resolveChange(Request $request, Task $task, ChangeRequest $changeRequest)
    {
        if (!auth()->user()->isGestor()) {
            abort(403);
        }

        $request->validate([
            'status' => ['required', 'in:aprovada,recusada'],
        ]);

        (new ResolveChangeRequest())->execute($changeRequest, auth()->user(), $request->status);

        return redirect()->back()->with('success', 'Solicitação resolvida.');
    }
}
