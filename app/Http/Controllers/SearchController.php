<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => ['required', 'string', 'min:2', 'max:100']]);

        $user = $request->user();
        $term = $request->input('q');

        $results = Task::search($term)
            ->query(fn (Builder $builder) => $builder->with('assignee')->visibleTo($user))
            ->take(8)
            ->get()
            ->map(fn ($task) => [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status,
                'priority' => $task->priority,
                'due_at' => optional($task->due_at)->format('d/m/Y H:i'),
                'assignee' => $task->assignee?->name,
                'url' => '/tarefas/'.$task->id,
            ]);

        return response()->json(['results' => $results]);
    }
}
