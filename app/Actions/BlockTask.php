<?php

namespace App\Actions;

use App\Events\TarefaBloqueada;
use App\Models\Task;
use App\Models\User;

class BlockTask
{
    public function execute(Task $task, User $actor, string $reason, string $blockedOn): Task
    {
        ChangeTaskStatus::change($task, $actor, 'bloqueada', [
            'block_reason' => $reason,
            'blocked_on' => $blockedOn,
        ]);

        $task->refresh();

        TarefaBloqueada::dispatch($task, $actor);

        return $task;
    }
}
