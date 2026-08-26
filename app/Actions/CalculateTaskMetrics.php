<?php

namespace App\Actions;

use App\Models\Task;

class CalculateTaskMetrics
{
    public static function isOverdue(Task $task): bool
    {
        if ($task->status === 'bloqueada') {
            return false;
        }

        return $task->isOverdue();
    }

    public static function overdueBy(Task $task): int
    {
        if (! static::isOverdue($task)) {
            return 0;
        }

        $timezone = $task->assignee?->timezone ?? 'America/Sao_Paulo';
        $now = now($timezone);

        return abs($now->diffInMinutes($task->due_at, false));
    }

    public static function timeUntilDue(Task $task): int
    {
        $timezone = $task->assignee?->timezone ?? 'America/Sao_Paulo';
        $now = now($timezone);

        if (! $task->due_at) {
            return 0;
        }

        return $now->diffInMinutes($task->due_at, false);
    }
}
