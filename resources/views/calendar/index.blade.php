@extends('layouts.app')

@php
$weekLabels = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];
$monthNames = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
$gridStart = $month->copy()->startOfMonth()->startOfWeek(\Carbon\CarbonInterface::MONDAY);
$priorityBadges = [
    'normal' => 'bg-slate-300 text-slate-700',
    'importante' => 'bg-brand-100 text-brand-700',
    'urgente' => 'bg-orange-100 text-orange-700',
    'critica' => 'bg-red-100 text-red-700',
];
@endphp

@section('title', 'Calendário - Gestão de Tarefas')

@section('content')
<div class="mx-auto max-w-[1800px] px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h1 class="text-2xl font-bold text-slate-900">{{ $monthNames[$month->month] }} de {{ $month->year }}</h1>
        <div class="flex items-center gap-3">
            <a href="{{ url('/calendario?mes='.$previousMonth->format('Y-m')) }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium hover:bg-slate-50">←</a>
            <a href="{{ url('/calendario') }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium hover:bg-slate-50">Hoje</a>
            <a href="{{ url('/calendario?mes='.$nextMonth->format('Y-m')) }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium hover:bg-slate-50">→</a>
            <button type="button" onclick="navigator.clipboard.writeText('{{ $feedUrl }}'); this.textContent='URL copiada!'; setTimeout(() => this.textContent='Copiar feed iCal', 2000)"
                    class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Copiar feed iCal
            </button>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="grid grid-cols-7 border-b border-slate-200 bg-slate-50">
            @foreach($weekLabels as $label)
                <div class="px-2 py-2.5 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</div>
            @endforeach
        </div>

        <div class="grid grid-cols-7">
            @for($day = $gridStart; $day < $month->addMonth()->startOfMonth(); $day = $day->addDay())
                @php
                    $isCurrentMonth = $day->isSameMonth($month);
                    $isToday = $day->isToday();
                    $dayTasks = $tasksByDay->get($day->format('Y-m-d'), collect());
                @endphp
                <div class="min-h-28 border-b border-r border-slate-100 p-1.5 {{ $isCurrentMonth ? '' : 'bg-slate-50/60' }}">
                    <span class="inline-flex size-6 items-center justify-center rounded-full text-xs font-semibold {{ $isToday ? 'bg-brand-700 text-white' : ($isCurrentMonth ? 'text-slate-600' : 'text-slate-300') }}">
                        {{ $day->format('j') }}
                    </span>
                    <div class="mt-1 space-y-1">
                        @foreach($dayTasks->take(4) as $task)
                            <a href="{{ route('tasks.show', $task) }}"
                               title="{{ $task->title }} · {{ $task->due_at->format('H:i') }}"
                               class="block truncate rounded-md px-1.5 py-1 text-[11px] font-medium {{ $priorityBadges[$task->priority] }} hover:opacity-80">
                                {{ $task->due_at->format('H:i') }} {{ $task->title }}
                                @if($task->isOverdue()) ⚠ @endif
                            </a>
                        @endforeach
                        @if($dayTasks->count() > 4)
                            <p class="px-1.5 text-[10px] font-semibold text-slate-400">+{{ $dayTasks->count() - 4 }} tarefas</p>
                        @endif
                    </div>
                </div>
            @endfor
        </div>
    </div>

    <p class="mt-3 text-xs text-slate-400">
        Feed iCal: use o botão "Copiar feed iCal" e cole no Google Agenda, Outlook ou Apple Calendar para sincronizar suas tarefas automaticamente.
    </p>
</div>
@endsection
