<?php

namespace App\Actions;

use App\Models\Task;
use App\Models\TaskHistoryEvent;
use App\Models\User;

class RecordHistory
{
    public static function record(Task $task, User $actor, string $eventType, array $payload = []): TaskHistoryEvent
    {
        return TaskHistoryEvent::create([
            'task_id' => $task->id,
            'actor_id' => $actor->id,
            'event_type' => $eventType,
            'payload' => $payload,
            'created_at' => now(),
        ]);
    }
}
