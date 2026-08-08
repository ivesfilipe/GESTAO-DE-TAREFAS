<?php

namespace App\Actions;

use App\Events\TarefaCriada;
use App\Models\Task;
use App\Models\User;

class CreateTask
{
    public function execute(User $creator, array $data): Task
    {
        $hasAssignee = isset($data['assigned_to']) && $data['assigned_to'] !== null;

        $task = Task::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'priority' => $data['priority'] ?? 'normal',
            'due_at' => $data['due_at'] ?? null,
            'original_due_at' => $data['due_at'] ?? null,
            'assigned_to' => $data['assigned_to'] ?? null,
            'created_by' => $creator->id,
            'status' => $hasAssignee ? 'nova' : 'nao_atribuida',
        ]);

        TarefaCriada::dispatch($task, $creator);

        return $task;
    }
}
