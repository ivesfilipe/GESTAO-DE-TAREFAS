<?php

namespace App\Actions;

use App\Events\TarefaAtribuida;
use App\Models\Task;
use App\Models\User;

class AssignTask
{
    public function execute(Task $task, User $actor, ?User $newAssignee): Task
    {
        $previousAssignee = $task->assignee;

        if ($newAssignee === null) {
            $task->update([
                'status' => 'nao_atribuida',
                'assigned_to' => null,
            ]);
        } else {
            $newStatus = $task->status === 'nao_atribuida' ? 'nova' : $task->status;
            $task->update([
                'status' => $newStatus,
                'assigned_to' => $newAssignee->id,
            ]);
        }

        $task->refresh();

        TarefaAtribuida::dispatch($task, $actor, $previousAssignee);

        return $task;
    }
}
