<?php

namespace App\Actions;

use App\Models\Task;

class UpdateTask
{
    public function execute(Task $task, array $data): Task
    {
        $fields = [
            'title',
            'description',
            'priority',
            'due_at',
            'assigned_to',
            'recurrence_frequency',
            'task_type',
            'acceptance_criteria',
            'expected_evidence',
        ];

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $task->{$field} = $data[$field];
            }
        }

        if ($task->isDirty('assigned_to')) {
            $task->status = $task->assigned_to === null ? 'nao_atribuida' : 'nova';
        }

        $task->save();

        return $task;
    }
}
