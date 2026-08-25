<?php

namespace App\Events\Concerns;

use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Str;

trait BroadcastsTaskActivity
{
    public function broadcastOn(): array
    {
        $task = $this->task->withoutRelations();

        $channels = [new PrivateChannel('task.'.$task->getKey())];

        foreach ([$task->assigned_to, $task->created_by] as $userId) {
            if ($userId !== null) {
                $channels[] = new PrivateChannel('user.'.$userId);
            }
        }

        return array_unique($channels, SORT_REGULAR);
    }

    public function broadcastAs(): string
    {
        return 'task.'.Str::snake((new \ReflectionClass($this))->getShortName());
    }

    public function broadcastWith(): array
    {
        $task = $this->task->withoutRelations();

        $assignee = User::find($task->assigned_to);

        return [
            'id' => $task->getKey(),
            'title' => $task->title,
            'status' => $task->status,
            'priority' => $task->priority,
            'due_at' => optional($task->due_at)->toISOString(),
            'assigned_to' => $task->assigned_to,
            'assignee_name' => $assignee?->name,
            'actor_name' => $this->actor->name,
        ];
    }
}
