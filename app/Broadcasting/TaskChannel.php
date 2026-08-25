<?php

namespace App\Broadcasting;

use App\Models\Task;
use App\Models\User;

class TaskChannel
{
    public function join(User $user, int $taskId): bool
    {
        $task = Task::find($taskId);

        if (! $task) {
            return false;
        }

        return $user->isGestor()
            || (int) $task->created_by === (int) $user->id
            || (int) $task->assigned_to === (int) $user->id;
    }
}
