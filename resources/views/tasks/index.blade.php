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

@section('title', 'Tarefas - Gestão de Tarefas')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h1 class="text-2xl font-bold text-slate-900">Tarefas</h1>
        <div class="flex items-center gap-4">
            @can('create-task')
                <a href="{{ route('tasks.create') }}" data-testid="nova-tarefa-desktop" class="hidden lg:inline-flex items-center gap-2 rounded-lg bg-brand-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 transition-colors">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nova Tarefa
                </a>
            @endcan
            <a href="{{ route('tasks.kanban') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">Ver quadro →</a>
        </div>
    </div>

    <form method="GET" action="{{ route('tasks.index') }}" class="mb-6 rounded-2xl bg-white/85 backdrop-blur border border-slate-200/60 p-5 shadow-sm animate-entrance">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label for="status" class="block text-xs font-medium text-slate-500 mb-1">Status</label>
                <select name="status" id="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none">
                    <option value="">Todos</option>
                    @foreach($statusLabels as $value => $label)
                        <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="priority" class="block text-xs font-medium text-slate-500 mb-1">Prioridade</label>
                <select name="priority" id="priority" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none">
                    <option value="">Todas</option>
                    <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="importante" {{ request('priority') === 'importante' ? 'selected' : '' }}>Importante</option>
                    <option value="urgente" {{ request('priority') === 'urgente' ? 'selected' : '' }}>Urgente</option>
                    <option value="critica" {{ request('priority') === 'critica' ? 'selected' : '' }}>Crítica</option>
                </select>
            </div>
            <div>
                <label for="search" class="block text-xs font-medium text-slate-500 mb-1">Busca</label>
                <input
                    type="text"
                    name="search"
                    id="search"
                    value="{{ request('search') }}"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none"
                    placeholder="Buscar por título..."
                />
            </div>
            @if(Auth::user()->isGestor())
                <div>
                    <label for="assigned_to" class="block text-xs font-medium text-slate-500 mb-1">Responsável</label>
                    <select name="assigned_to" id="assigned_to" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none">
                        <option value="">Todos</option>
                        @foreach($teamMembers ?? [] as $member)
                            <option value="{{ $member->id }}" {{ request('assigned_to') == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>
        <div class="mt-3 flex gap-3">
            <button type="submit" class="rounded-xl bg-gradient-to-br from-brand-600 to-brand-700 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all">
                Filtrar
            </button>
            <a href="{{ route('tasks.index') }}" class="rounded-xl border border-slate-200 bg-white px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:shadow-sm transition-all">
                Limpar
            </a>
        </div>
    </form>

    <div class="lg:hidden space-y-3">
        @forelse($tasks as $task)
            <a href="{{ route('tasks.show', $task) }}" class="block rounded-xl bg-white border border-slate-200 p-4 shadow-sm hover:shadow-md hover:border-brand-200 transition-all">
                <div class="flex items-start justify-between gap-2">
                    <h3 class="font-semibold text-slate-900 leading-snug">{{ $task->title }} @if($task->isRecurring())<span class="text-violet-600">↻</span>@endif</h3>
                    <span class="shrink-0 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $priorityBadges[$task->priority] }}">
                        {{ ucfirst($task->priority) }}
                    </span>
                </div>
                <div class="mt-2 flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusBadges[$task->status] }}">
                        {{ $statusLabels[$task->status] }}
                    </span>
                    @if($task->assignee)
                        <span class="text-xs text-slate-500">{{ $task->assignee->name }}</span>
                    @endif
                </div>
                @if($task->due_at)
                    <div class="mt-2 flex items-center gap-1 text-xs {{ $task->isOverdue() ? 'text-red-600' : 'text-slate-500' }}">
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        {{ $task->due_at->format('d/m/Y H:i') }}
                        @if($task->isOverdue())
                            <span class="font-medium">Atrasada</span>
                        @endif
                    </div>
                @endif
            </a>
        @empty
            <div class="rounded-xl bg-white border border-slate-200 p-12 text-center shadow-sm">
                <svg class="mx-auto size-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <h3 class="mt-3 text-sm font-medium text-slate-900">Nenhuma tarefa encontrada</h3>
                <p class="mt-1 text-sm text-slate-500">Ajuste os filtros ou crie uma nova tarefa.</p>
            </div>
        @endforelse
    </div>

    <div class="hidden lg:block rounded-2xl bg-white/90 backdrop-blur border border-slate-200/60 shadow-sm overflow-hidden animate-entrance stagger-1">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50">
                    <th class="px-6 py-3 text-left font-medium text-slate-500">Título</th>
                    <th class="px-6 py-3 text-left font-medium text-slate-500">Prioridade</th>
                    <th class="px-6 py-3 text-left font-medium text-slate-500">Status</th>
                    <th class="px-6 py-3 text-left font-medium text-slate-500">Responsável</th>
                    <th class="px-6 py-3 text-left font-medium text-slate-500">Prazo</th>
                    <th class="px-6 py-3 text-right font-medium text-slate-500">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($tasks as $task)
                    <tr class="hover:bg-slate-50/50 cursor-pointer" onclick="window.location='{{ route('tasks.show', $task) }}'">
                        <td class="px-6 py-4 font-medium text-slate-900">
                            {{ $task->title }}
                            @if($task->isRecurring())
                                <span class="ml-1 text-violet-600" title="Tarefa recorrente">↻</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $priorityBadges[$task->priority] }}">
                                {{ ucfirst($task->priority) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusBadges[$task->status] }}">
                                {{ $statusLabels[$task->status] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-500">
                            {{ $task->assignee?->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4 {{ $task->isOverdue() ? 'text-red-600 font-medium' : 'text-slate-500' }}">
                            {{ $task->due_at?->format('d/m/Y H:i') ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('tasks.show', $task) }}" class="text-brand-500 hover:text-brand-600 text-sm font-medium">Ver</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <svg class="mx-auto size-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <h3 class="mt-3 text-sm font-medium text-slate-900">Nenhuma tarefa encontrada</h3>
                            <p class="mt-1 text-sm text-slate-500">Ajuste os filtros ou crie uma nova tarefa.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $tasks->links() }}
    </div>
</div>

@if(Auth::user()->isGestor())
    <a href="{{ route('tasks.create') }}" class="fixed bottom-24 right-4 z-30 lg:bottom-8 lg:hidden flex items-center justify-center size-14 rounded-full bg-brand-700 text-white shadow-lg hover:bg-brand-600 transition-colors">
        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
    </a>
@endif
@endsection
