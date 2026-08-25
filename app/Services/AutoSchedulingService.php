<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Carbon\CarbonImmutable;

class AutoSchedulingService
{
    public function __construct(
        private readonly int $dayStart = 9,
        private readonly int $lunchStart = 12,
        private readonly int $lunchEnd = 13,
        private readonly int $dayEnd = 18,
    ) {}

    public function propose(User $user, ?CarbonImmutable $from = null): array
    {
        $cursor = $this->firstWorkMoment($from ?? CarbonImmutable::now());

        $tasks = Task::query()
            ->whereNotIn('status', ['cancelada', 'concluida'])
            ->whereNull('scheduled_start')
            ->when(! $user->isGestor(), fn ($q) => $q->where('assigned_to', $user->id))
            ->orderByRaw('CASE priority WHEN \'critica\' THEN 1 WHEN \'urgente\' THEN 2 WHEN \'importante\' THEN 3 ELSE 4 END')
            ->orderBy('due_at')
            ->limit(50)
            ->get();

        $blocks = [];

        foreach ($tasks as $task) {
            [$start, $end] = $this->allocate($cursor, $this->durationFor($task));

            if ($start === null) {
                break;
            }

            $blocks[] = [
                'task_id' => $task->id,
                'title' => $task->title,
                'priority' => $task->priority,
                'due_at' => optional($task->due_at)->format('d/m H:i'),
                'start' => $start,
                'end' => $end,
                'overdue' => $task->isOverdue(),
            ];

            $cursor = $this->firstWorkMoment($end);
        }

        return $blocks;
    }

    public function apply(User $user, array $blocks): int
    {
        $applied = 0;

        foreach ($blocks as $block) {
            $task = Task::find((int) ($block['task_id'] ?? 0));

            if (! $task || ! isset($block['start'])) {
                continue;
            }

            if (! $user->isGestor() && (int) $task->assigned_to !== (int) $user->id) {
                continue;
            }

            $task->update(['scheduled_start' => CarbonImmutable::parse($block['start'])]);
            $applied++;
        }

        return $applied;
    }

    private function allocate(CarbonImmutable $cursor, int $minutes): array
    {
        for ($guard = 0; $guard < 400; $guard++) {
            $windowEnd = $this->currentWindowEnd($cursor);

            if ($windowEnd === null) {
                $cursor = $this->nextDayStart($cursor);

                continue;
            }

            $proposedEnd = $cursor->addMinutes($minutes);

            if ($proposedEnd->lessThanOrEqualTo($windowEnd)) {
                return [$cursor, $proposedEnd];
            }

            $cursor = $this->firstWorkMoment($windowEnd);
        }

        return [null, null];
    }

    private function durationFor(Task $task): int
    {
        return max(30, (int) ($task->estimated_minutes ?? 60));
    }

    private function firstWorkMoment(CarbonImmutable $time): CarbonImmutable
    {
        if ((int) $time->format('N') >= 6) {
            do {
                $time = $time->addDay();
            } while ((int) $time->format('N') >= 6);

            return $time->setTime($this->dayStart, 0);
        }

        $minutesOfDay = ((int) $time->format('H')) * 60 + (int) $time->format('i');

        if ($minutesOfDay < $this->dayStart * 60) {
            return $time->setTime($this->dayStart, 0);
        }

        if ($minutesOfDay >= $this->lunchStart * 60 && $minutesOfDay < $this->lunchEnd * 60) {
            return $time->setTime($this->lunchEnd, 0);
        }

        if ($minutesOfDay >= $this->dayEnd * 60) {
            return $this->firstWorkMoment($time->addDay()->startOfDay());
        }

        return $time->setSecond(0);
    }

    private function currentWindowEnd(CarbonImmutable $time): ?CarbonImmutable
    {
        $minutesOfDay = ((int) $time->format('H')) * 60 + (int) $time->format('i');

        if ($minutesOfDay < $this->lunchStart * 60) {
            return $time->setTime($this->lunchStart, 0);
        }

        if ($minutesOfDay < $this->dayEnd * 60) {
            return $time->setTime($this->dayEnd, 0);
        }

        return null;
    }

    private function nextDayStart(CarbonImmutable $time): CarbonImmutable
    {
        return $this->firstWorkMoment($time->addDay()->startOfDay());
    }
}
