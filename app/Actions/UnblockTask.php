<?php

namespace App\Actions;

use App\Events\TarefaDesbloqueada;
use App\Models\Task;
use App\Models\User;

class UnblockTask
{
    public function execute(Task $task, User $actor): Task
    {
        ChangeTaskStatus::change($task, $actor, 'em_andamento');

        $task->refresh();

        TarefaDesbloqueada::dispatch($task, $actor);

        return $task;
    }
}
