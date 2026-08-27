@extends('layouts.app')

@php
$priorityBadges = [
    'normal' => 'bg-slate-100 text-slate-700 border border-slate-200',
    'importante' => 'bg-brand-50 text-brand-700 border border-brand-200',
    'urgente' => 'bg-orange-50 text-orange-700 border border-orange-200',
    'critica' => 'bg-red-50 text-red-700 border border-red-200',
];
$statusLabels = [
    'nao_atribuida' => 'Não atribuída',
    'nova' => 'Nova',
    'recebida' => 'Recebida',
    'em_andamento' => 'Em andamento',
    'aguardando_aprovacao' => 'Aguardando aprovação',
    'concluida' => 'Concluída',
    'bloqueada' => 'Bloqueada',
    'reprovada' => 'Reprovada',
    'cancelada' => 'Cancelada',
];
$statusBadges = [
    'nao_atribuida' => 'bg-gray-50 text-gray-600 border border-gray-200',
    'nova' => 'bg-brand-50 text-brand-700 border border-brand-200',
    'recebida' => 'bg-indigo-50 text-indigo-700 border border-indigo-200',
    'em_andamento' => 'bg-amber-50 text-amber-700 border border-amber-200',
    'aguardando_aprovacao' => 'bg-violet-50 text-violet-700 border border-violet-200',
    'concluida' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
    'bloqueada' => 'bg-amber-100 text-amber-800 border border-amber-200',
    'reprovada' => 'bg-red-50 text-red-700 border border-red-200',
    'cancelada' => 'bg-gray-100 text-gray-500 border border-gray-200',
];
@endphp

@section('title', 'Minhas Tarefas - Gestão de Tarefas')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-6 animate-fade-in-up">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Minhas Tarefas</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Foco no agora · priorize o que importa</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-orange-50/90 via-orange-50/60 to-amber-50/50 dark:from-orange-500/[0.08] dark:via-orange-500/[0.04] dark:to-amber-500/[0.06] backdrop-blur border border-orange-200/60 dark:border-orange-500/20 p-6 shadow-sm dark:shadow-none hover-lift animate-entrance stagger-1">
            <div class="pointer-events-none absolute -top-12 -right-12 size-32 rounded-full bg-orange-200/30 blur-2xl"></div>
            <div class="relative flex items-center justify-between">
                <h2 class="font-semibold tracking-tight text-slate-900 dark:text-white flex items-center gap-2.5">
                    <span class="flex size-8 items-center justify-center rounded-xl bg-gradient-to-br from-orange-500 to-amber-500 text-white shadow-sm shadow-orange-500/20">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </span>
                    Urgentes
                    @if(($urgentes ?? collect())->count() > 0)
                        <span class="ml-1 inline-flex size-6 items-center justify-center rounded-full bg-orange-500 text-[11px] font-bold text-white shadow-sm">{{ ($urgentes ?? collect())->count() }}</span>
                    @endif
                </h2>
            </div>
            <div class="relative mt-5 space-y-3">
                @forelse($urgentes ?? [] as $idx => $task)
                    <a href="{{ route('tasks.show', $task) }}" class="group block rounded-xl bg-white/90 dark:bg-slate-900/60 backdrop-blur border border-orange-200/60 dark:border-orange-500/20 p-4 shadow-sm dark:shadow-none hover:shadow-md hover:border-orange-300 dark:hover:border-orange-400/30 hover:-translate-y-0.5 transition-all animate-entrance" style="animation-delay: {{ 100 + $idx*70 }}ms">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="text-sm font-semibold leading-snug text-slate-900 dark:text-white group-hover:text-orange-700 dark:group-hover:text-orange-300 transition-colors">{{ $task->title }}</h3>
                            <span class="shrink-0 inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $priorityBadges[$task->priority] }}">
                                {{ ucfirst($task->priority) }}
                            </span>
                        </div>
                        <div class="mt-2.5 flex items-center gap-1.5 flex-wrap">
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-medium {{ $statusBadges[$task->status] }}">
                                {{ $statusLabels[$task->status] }}
                            </span>
                        </div>
                        @if($task->due_at)
                            <div class="mt-2.5 flex items-center gap-1.5 text-xs {{ $task->isOverdue() ? 'text-red-600 font-semibold' : 'text-slate-500' }}">
                                <svg class="size-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $task->due_at->format('d/m/Y H:i') }}
                                @if($task->isOverdue())
                                    <span class="ml-1 inline-flex items-center gap-1 rounded-full bg-red-500 px-2 py-0.5 text-[10px] font-bold text-white shadow-sm"><span class="size-1 rounded-full bg-white animate-glow"></span>Atrasada</span>
                                @endif
                            </div>
                        @endif
                    </a>
                @empty
                    <div class="rounded-xl border border-dashed border-orange-300/50 dark:border-orange-500/20 bg-white/60 dark:bg-white/[0.03] p-6 text-center">
                        <p class="text-sm font-medium text-orange-700 dark:text-orange-300">Nenhuma tarefa urgente.</p>
                        <p class="mt-1 text-xs text-orange-600/70 dark:text-orange-300/60">Ótimo — mantenha o foco nas entregas de hoje.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-brand-50/90 via-sky-50/50 to-white/80 dark:from-brand-500/[0.08] dark:via-sky-500/[0.04] dark:to-white/[0.03] backdrop-blur border border-brand-200/60 dark:border-brand-500/20 p-6 shadow-sm dark:shadow-none hover-lift animate-entrance stagger-2">
            <div class="pointer-events-none absolute -top-12 -right-12 size-32 rounded-full bg-brand-200/25 blur-2xl"></div>
            <div class="relative flex items-center justify-between">
                <h2 class="font-semibold tracking-tight text-slate-900 dark:text-white flex items-center gap-2.5">
                    <span class="flex size-8 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-sm shadow-brand-500/20">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </span>
                    Hoje
                    @if(($hoje ?? collect())->count() > 0)
                        <span class="ml-1 inline-flex size-6 items-center justify-center rounded-full bg-brand-500 text-[11px] font-bold text-white shadow-sm">{{ ($hoje ?? collect())->count() }}</span>
                    @endif
                </h2>
            </div>
            <div class="relative mt-5 space-y-3">
                @forelse($hoje ?? [] as $idx => $task)
                    <a href="{{ route('tasks.show', $task) }}" class="group block rounded-xl bg-white/90 dark:bg-slate-900/60 backdrop-blur border border-brand-200/50 dark:border-brand-500/20 p-4 shadow-sm dark:shadow-none hover:shadow-md hover:border-brand-300 dark:hover:border-brand-400/30 hover:-translate-y-0.5 transition-all animate-entrance" style="animation-delay: {{ 160 + $idx*70 }}ms">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="text-sm font-semibold leading-snug text-slate-900 dark:text-white group-hover:text-brand-700 dark:group-hover:text-brand-300 transition-colors">{{ $task->title }}</h3>
                            <span class="shrink-0 inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $priorityBadges[$task->priority] }}">
                                {{ ucfirst($task->priority) }}
                            </span>
                        </div>
                        <div class="mt-2.5 flex items-center gap-1.5 flex-wrap">
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-medium {{ $statusBadges[$task->status] }}">
                                {{ $statusLabels[$task->status] }}
                            </span>
                        </div>
                        @if($task->due_at)
                            <div class="mt-2.5 flex items-center gap-1.5 text-xs text-slate-500">
                                <svg class="size-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $task->due_at->format('d/m/Y H:i') }}
                            </div>
                        @endif
                    </a>
                @empty
                    <div class="rounded-xl border border-dashed border-brand-200/50 dark:border-brand-500/20 bg-white/60 dark:bg-white/[0.03] p-6 text-center">
                        <p class="text-sm font-medium text-brand-700 dark:text-brand-300">Nada para hoje.</p>
                        <p class="mt-1 text-xs text-brand-600/70 dark:text-brand-300/60">Tudo em dia — aproveite para adiantar próximas.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl bg-white/80 dark:bg-white/[0.04] backdrop-blur border border-slate-200/60 dark:border-white/10 p-6 shadow-sm dark:shadow-none hover-lift animate-entrance stagger-3">
            <div class="pointer-events-none absolute -top-12 -right-12 size-32 rounded-full bg-slate-200/30 blur-2xl"></div>
            <div class="relative flex items-center justify-between">
                <h2 class="font-semibold tracking-tight text-slate-900 dark:text-white flex items-center gap-2.5">
                    <span class="flex size-8 items-center justify-center rounded-xl bg-slate-900 text-white shadow-sm">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                    </span>
                    Próximas
                </h2>
                @if(($proximas ?? collect())->count() > 0)
                    <span class="inline-flex size-6 items-center justify-center rounded-full bg-slate-900 text-[11px] font-bold text-white">{{ ($proximas ?? collect())->count() }}</span>
                @endif
            </div>
            <div class="relative mt-5 space-y-3">
                @forelse($proximas ?? [] as $idx => $task)
                    <a href="{{ route('tasks.show', $task) }}" class="group block rounded-xl bg-white dark:bg-slate-900/60 border border-slate-200/70 dark:border-white/10 p-4 shadow-sm dark:shadow-none hover:shadow-md hover:border-slate-300 dark:hover:border-white/20 hover:-translate-y-0.5 transition-all animate-entrance" style="animation-delay: {{ 220 + $idx*70 }}ms">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="text-sm font-semibold leading-snug text-slate-900 dark:text-white group-hover:text-brand-700 dark:group-hover:text-brand-300 transition-colors">{{ $task->title }}</h3>
                            <span class="shrink-0 inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $priorityBadges[$task->priority] }}">
                                {{ ucfirst($task->priority) }}
                            </span>
                        </div>
                        <div class="mt-2.5 flex items-center gap-1.5 flex-wrap">
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-medium {{ $statusBadges[$task->status] }}">
                                {{ $statusLabels[$task->status] }}
                            </span>
                        </div>
                        @if($task->due_at)
                            <div class="mt-2.5 flex items-center gap-1.5 text-xs text-slate-500">
                                <svg class="size-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $task->due_at->format('d/m/Y H:i') }}
                            </div>
                        @endif
                    </a>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300/60 dark:border-white/10 bg-slate-50/60 dark:bg-white/[0.03] p-6 text-center">
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-300">Nenhuma tarefa pendente.</p>
                        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Quando novas tarefas chegarem, aparecem aqui.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
