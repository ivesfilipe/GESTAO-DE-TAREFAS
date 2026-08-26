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
            'recurrence_frequency' => $data['recurrence_frequency'] ?? null,
            'recurrence_next_at' => $data['recurrence_next_at'] ?? null,
            'recurrence_series_id' => $data['recurrence_series_id'] ?? null,
            'task_type' => $data['task_type'] ?? 'demanda',
            'acceptance_criteria' => $data['acceptance_criteria'] ?? null,
            'expected_evidence' => $data['expected_evidence'] ?? null,
        ]);

        TarefaCriada::dispatch($task, $creator);

        return $task;
    }
}
