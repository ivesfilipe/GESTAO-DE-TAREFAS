<?php

namespace App\Actions;

use App\Events\TarefaAprovada;
use App\Models\Task;
use App\Models\User;

class ApproveTask
{
    public function execute(Task $task, User $approver): Task
    {
        ChangeTaskStatus::change($task, $approver, 'concluida', [
            'approved_by' => $approver->id,
        ]);

        $task->refresh();

        TarefaAprovada::dispatch($task, $approver);

        return $task;
    }
}
