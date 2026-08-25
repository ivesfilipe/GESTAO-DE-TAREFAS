<?php

namespace App\Http\Controllers\Api;

use App\Actions\AddComment;
use App\Actions\ChangeTaskStatus;
use App\Actions\CreateTask;
use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TaskApiController extends Controller
{
    public function index(Request $request): ResourceCollection
    {
        $user = $request->user();

        $tasks = Task::query()
            ->with(['assignee:id,name,email', 'creator:id,name,email'])
            ->when(! $user->isGestor(), fn ($q) => $q->where('assigned_to', $user->id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->input('priority')))
            ->when($request->filled('assigned_to'), fn ($q) => $q->where('assigned_to', $request->input('assigned_to')))
            ->latest()
            ->paginate(min((int) $request->input('per_page', 25), 100));

        return new ResourceCollection($tasks);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'max:255'],
            'description' => ['nullable'],
            'priority' => ['required', 'in:normal,importante,urgente,critica'],
            'due_at' => ['required', 'date', 'after:now'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'recurrence_frequency' => ['nullable', 'in:diaria,semanal,quinzenal,mensal'],
        ]);

        $task = (new CreateTask)->execute($request->user(), $data);

        return response()->json([
            'data' => $task->load('assignee:id,name,email'),
        ], 201);
    }

    public function show(Request $request, Task $task): JsonResponse
    {
        $this->authorizeAccess($request, $task);

        return response()->json([
            'data' => $task->load(['assignee:id,name,email', 'comments.author:id,name']),
        ]);
    }

    public function updateStatus(Request $request, Task $task): JsonResponse
    {
        $this->authorizeAccess($request, $task);

        if (! $request->user()->isGestor() && (int) $task->assigned_to !== (int) $request->user()->id) {
            return response()->json(['message' => 'Apenas o responsável pode mover esta tarefa.'], 403);
        }

        $request->validate(['status' => ['required', 'in:'.implode(',', Task::statuses())]]);

        try {
            ChangeTaskStatus::change($task, $request->user(), $request->input('status'));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => $task->fresh()]);
    }

    public function addComment(Request $request, Task $task): JsonResponse
    {
        $this->authorizeAccess($request, $task);

        $request->validate(['body' => ['required', 'string', 'max:5000']]);

        $comment = (new AddComment)->execute($task, $request->user(), $request->input('body'));

        return response()->json(['data' => $comment], 201);
    }

    private function authorizeAccess(Request $request, Task $task): void
    {
        $user = $request->user();

        if ($user->isGestor()) {
            return;
        }

        if ((int) $task->assigned_to !== (int) $user->id && (int) $task->created_by !== (int) $user->id) {
            abort(403, 'Você não tem acesso a esta tarefa.');
        }
    }
}
