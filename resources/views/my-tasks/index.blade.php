@extends('layouts.app')

@php
$priorityBadges = [
    'normal' => 'bg-slate-300 text-slate-700',
    'importante' => 'bg-brand-100 text-brand-600',
    'urgente' => 'bg-orange-100 text-orange-700',
    'critica' => 'bg-red-100 text-red-700',
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
    'nao_atribuida' => 'bg-gray-100 text-gray-600',
    'nova' => 'bg-brand-100 text-brand-600',
    'recebida' => 'bg-indigo-100 text-indigo-700',
    'em_andamento' => 'bg-yellow-100 text-yellow-700',
    'aguardando_aprovacao' => 'bg-purple-100 text-purple-700',
    'concluida' => 'bg-green-100 text-green-700',
    'bloqueada' => 'bg-yellow-200 text-yellow-800',
    'reprovada' => 'bg-red-100 text-red-700',
    'cancelada' => 'bg-gray-200 text-gray-500',
];
@endphp

@section('title', 'Minhas Tarefas - Gestão de Tarefas')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
    <h1 class="text-2xl font-bold text-slate-900 mb-2">Minhas Tarefas</h1>
    <p class="text-sm text-slate-500 mb-6">O que eu preciso fazer agora?</p>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="rounded-xl bg-orange-50 border border-orange-200 p-6 shadow-sm">
            <h2 class="font-semibold text-slate-900 flex items-center gap-2">
                <svg class="size-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Urgentes
            </h2>
            <div class="mt-4 space-y-3">
                @forelse($urgentes ?? [] as $task)
                    <a href="{{ route('tasks.show', $task) }}" class="block rounded-lg bg-white border border-orange-200 p-4 hover:shadow-md transition-all">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="text-sm font-semibold text-slate-900">{{ $task->title }}</h3>
                            <span class="shrink-0 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $priorityBadges[$task->priority] }}">
                                {{ ucfirst($task->priority) }}
                            </span>
                        </div>
                        <div class="mt-2 flex items-center gap-2">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $statusBadges[$task->status] }}">
                                {{ $statusLabels[$task->status] }}
                            </span>
                        </div>
                        @if($task->due_at)
                            <div class="mt-2 flex items-center gap-1 text-xs {{ $task->isOverdue() ? 'text-red-600 font-medium' : 'text-slate-500' }}">
                                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $task->due_at->format('d/m/Y H:i') }}
                                @if($task->isOverdue())
                                    <span>Atrasada</span>
                                @endif
                            </div>
                        @endif
                    </a>
                @empty
                    <p class="text-sm text-orange-600 italic">Nenhuma tarefa urgente.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl bg-brand-50 border border-brand-200 p-6 shadow-sm">
            <h2 class="font-semibold text-slate-900 flex items-center gap-2">
                <svg class="size-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Hoje
            </h2>
            <div class="mt-4 space-y-3">
                @forelse($hoje ?? [] as $task)
                    <a href="{{ route('tasks.show', $task) }}" class="block rounded-lg bg-white border border-brand-200 p-4 hover:shadow-md transition-all">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="text-sm font-semibold text-slate-900">{{ $task->title }}</h3>
                            <span class="shrink-0 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $priorityBadges[$task->priority] }}">
                                {{ ucfirst($task->priority) }}
                            </span>
                        </div>
                        <div class="mt-2 flex items-center gap-2">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $statusBadges[$task->status] }}">
                                {{ $statusLabels[$task->status] }}
                            </span>
                        </div>
                        @if($task->due_at)
                            <div class="mt-2 flex items-center gap-1 text-xs text-slate-500">
                                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $task->due_at->format('d/m/Y H:i') }}
                            </div>
                        @endif
                    </a>
                @empty
                    <p class="text-sm text-brand-500 italic">Nada para hoje.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
            <h2 class="font-semibold text-slate-900 flex items-center gap-2">
                <svg class="size-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
                Próximas
            </h2>
            <div class="mt-4 space-y-3">
                @forelse($proximas ?? [] as $task)
                    <a href="{{ route('tasks.show', $task) }}" class="block rounded-lg border border-slate-200 p-4 hover:shadow-md hover:border-brand-200 transition-all">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="text-sm font-semibold text-slate-900">{{ $task->title }}</h3>
                            <span class="shrink-0 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $priorityBadges[$task->priority] }}">
                                {{ ucfirst($task->priority) }}
                            </span>
                        </div>
                        <div class="mt-2 flex items-center gap-2">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $statusBadges[$task->status] }}">
                                {{ $statusLabels[$task->status] }}
                            </span>
                        </div>
                        @if($task->due_at)
                            <div class="mt-2 flex items-center gap-1 text-xs text-slate-500">
                                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $task->due_at->format('d/m/Y H:i') }}
                            </div>
                        @endif
                    </a>
                @empty
                    <p class="text-sm text-slate-400 italic">Nenhuma tarefa pendente.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
