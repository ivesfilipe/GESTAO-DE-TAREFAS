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
            'acceptance_criteria' => $this->normalizeList($data['acceptance_criteria'] ?? null),
            'expected_evidence' => $this->normalizeList($data['expected_evidence'] ?? null),
        ]);

        TarefaCriada::dispatch($task, $creator);

        return $task;
    }

    private function normalizeList(array|string|null $value): ?array
    {
        if (is_array($value)) {
            $items = $value;
        } elseif (is_string($value)) {
            $items = preg_split('/\R/', $value) ?: [];
        } else {
            return null;
        }

        $items = array_values(array_filter(array_map(
            fn ($item) => preg_replace('/^\s*(?:\d+[.)]|[-*])\s*/u', '', trim((string) $item)) ?? '',
            $items,
        )));

        return $items === [] ? null : $items;
    }
}
