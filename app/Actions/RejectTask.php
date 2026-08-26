<?php

namespace App\Actions;

use App\Events\TarefaReprovada;
use App\Models\Task;
use App\Models\User;

class RejectTask
{
    public function execute(Task $task, User $approver, string $category, string $note): Task
    {
        $validCategories = Task::rejectionCategories();

        if (! in_array($category, $validCategories, true)) {
            throw new \InvalidArgumentException(
                "Categoria de rejeição inválida: '{$category}'"
            );
        }

        ChangeTaskStatus::change($task, $approver, 'reprovada', [
            'rejection_category' => $category,
            'rejection_note' => $note,
        ]);

        $task->refresh();

        TarefaReprovada::dispatch($task, $approver);

        return $task;
    }
}
