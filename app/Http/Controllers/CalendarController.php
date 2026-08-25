<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $month = $this->resolveMonth($request->input('mes'));
        $user = $request->user();

        $tasks = $this->visibleTasks($user)
            ->whereBetween('due_at', [$month->startOfMonth(), $month->endOfMonth()])
            ->get()
            ->groupBy(fn (Task $task) => $task->due_at->format('Y-m-d'));

        return view('calendar.index', [
            'month' => $month,
            'previousMonth' => $month->subMonth(),
            'nextMonth' => $month->addMonth(),
            'tasksByDay' => $tasks,
            'feedUrl' => route('calendar.ical', ['token' => $user->calendar_token]),
        ]);
    }

    public function ical(string $token)
    {
        $user = User::where('calendar_token', $token)->firstOrFail();

        $tasks = $this->visibleTasks($user)
            ->whereNotNull('due_at')
            ->orderBy('due_at')
            ->limit(500)
            ->get();

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Gestao de Tarefas//PT-BR',
            'CALSCALE:GREGORIAN',
            'X-WR-CALNAME:Tarefas - '.$user->name,
        ];

        foreach ($tasks as $task) {
            $start = $task->due_at->copy()->setTimezone('UTC');
            $end = $start->copy()->addHour();

            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:task-'.$task->id.'@gestao-tarefas';
            $lines[] = 'DTSTAMP:'.$task->created_at?->copy()->setTimezone('UTC')->format('Ymd\THis\Z');
            $lines[] = 'DTSTART:'.$start->format('Ymd\THis\Z');
            $lines[] = 'DTEND:'.$end->format('Ymd\THis\Z');
            $lines[] = 'SUMMARY:'.$this->escapeIcal($task->title);
            if ($task->description) {
                $lines[] = 'DESCRIPTION:'.$this->escapeIcal(mb_substr($task->description, 0, 200));
            }
            $lines[] = 'STATUS:'.match ($task->status) {
                'concluida', 'cancelada' => 'CONFIRMED',
                default => 'TENTATIVE',
            };
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        return response(implode("\r\n", $lines), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="tarefas.ics"',
        ]);
    }

    private function visibleTasks(User $user)
    {
        return Task::query()
            ->when(! $user->isGestor(), fn ($q) => $q->where('assigned_to', $user->id))
            ->whereNotIn('status', ['cancelada']);
    }

    private function resolveMonth(?string $value): CarbonImmutable
    {
        try {
            $month = CarbonImmutable::createFromFormat('Y-m', $value ?? '');
        } catch (\Throwable) {
            $month = false;
        }

        return $month === false ? CarbonImmutable::now()->startOfMonth() : $month->startOfMonth();
    }

    private function escapeIcal(string $value): string
    {
        return str_replace(["\r\n", "\n", "\r", ',', ';'], ['\\n', '\\n', '\\n', '\\,', '\\;'], $value);
    }
}
