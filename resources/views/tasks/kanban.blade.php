@extends('layouts.app')

@php
$columnAccents = [
    'nao_atribuida' => 'border-slate-300',
    'nova' => 'border-brand-400',
    'recebida' => 'border-indigo-400',
    'em_andamento' => 'border-amber-400',
    'bloqueada' => 'border-red-400',
    'aguardando_aprovacao' => 'border-violet-400',
    'reprovada' => 'border-rose-400',
    'concluida' => 'border-green-400',
];
$priorityBadges = [
    'normal' => 'bg-slate-300 text-slate-700',
    'importante' => 'bg-brand-100 text-brand-700',
    'urgente' => 'bg-orange-100 text-orange-700',
    'critica' => 'bg-red-100 text-red-700',
];
@endphp

@section('title', 'Quadro de Tarefas - Gestão de Tarefas')

@section('content')
<div class="mx-auto max-w-[1800px] px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h1 class="text-2xl font-bold text-slate-900">Quadro de Tarefas</h1>
        <div class="flex items-center gap-3">
            @if(Auth::user()->isGestor())
                <form method="GET" action="{{ route('tasks.kanban') }}">
                    <select name="assigned_to" onchange="this.form.submit()" class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-brand-500 outline-none">
                        <option value="">Toda a equipe</option>
                        @foreach($liderados as $liderado)
                            <option value="{{ $liderado->id }}" {{ request('assigned_to') == $liderado->id ? 'selected' : '' }}>{{ $liderado->name }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
            <a href="{{ route('tasks.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Ver em lista</a>
        </div>
    </div>

    <p class="mb-4 text-sm text-slate-500">
        Arraste os cards para mover as tarefas de etapa — apenas transições permitidas para você serão aceitas.
    </p>

    <div class="flex gap-4 overflow-x-auto pb-4 items-start">
        @foreach($columns as $status => $label)
            <div class="kanban-column shrink-0 w-72 bg-slate-100 rounded-xl border-t-4 {{ $columnAccents[$status] }} flex flex-col max-h-[75vh]">
                <div class="flex items-center justify-between px-4 py-3">
                    <h2 class="text-sm font-semibold text-slate-700">{{ $label }}</h2>
                    <span class="rounded-full bg-white px-2 py-0.5 text-xs font-semibold text-slate-500 border border-slate-200">
                        {{ ($tasks[$status] ?? collect())->count() }}
                    </span>
                </div>

                <div class="kanban-cards flex-1 overflow-y-auto px-3 pb-3 space-y-2.5 min-h-[80px]"
                     data-status="{{ $status }}">
                    @forelse(($tasks[$status] ?? collect()) as $task)
                        <div class="kanban-card bg-white rounded-lg border border-slate-200 p-3 shadow-sm cursor-grab active:cursor-grabbing hover:shadow-md transition-shadow"
                             data-task-id="{{ $task->id }}"
                             data-status="{{ $task->status }}">
                            <div class="flex items-start justify-between gap-2">
                                <a href="{{ route('tasks.show', $task) }}" class="text-sm font-semibold text-slate-900 leading-snug hover:text-brand-600">
                                    {{ $task->title }}
                                    @if($task->isRecurring())<span class="text-violet-600" title="Recorrente">↻</span>@endif
                                </a>
                            </div>
                            <div class="mt-2 flex items-center gap-1.5 flex-wrap">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $priorityBadges[$task->priority] }}">
                                    {{ ucfirst($task->priority) }}
                                </span>
                                @if($task->isOverdue())
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold bg-red-600 text-white">Atrasada</span>
                                @endif
                            </div>
                            <div class="mt-2 flex items-center justify-between text-xs text-slate-400">
                                <span>{{ $task->assignee?->name ?? 'Sem responsável' }}</span>
                                <span class="{{ $task->isOverdue() ? 'text-red-600 font-medium' : '' }}">
                                    {{ $task->due_at?->format('d/m') ?? '' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 italic text-center pt-4">Solte um card aqui</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>

<div id="kanban-toast" class="fixed bottom-24 right-4 z-50 hidden max-w-sm rounded-lg px-4 py-3 text-sm font-medium shadow-lg"></div>

@push('scripts')
<script src="{{ asset('js/sortable.min.js') }}"></script>
<script>
    (function () {
        var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var toast = document.getElementById('kanban-toast');
        var toastTimer = null;

        function showToast(message, isError) {
            toast.textContent = message;
            toast.className = 'fixed bottom-24 right-4 z-50 max-w-sm rounded-lg px-4 py-3 text-sm font-medium shadow-lg ' +
                (isError ? 'bg-red-600 text-white' : 'bg-green-600 text-white');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(function () { toast.classList.add('hidden'); }, 4000);
        }

        var TRANSITIONS = {
            'nao_atribuida': ['nova'],
            'nova': ['recebida'],
            'recebida': ['em_andamento'],
            'em_andamento': ['aguardando_aprovacao', 'bloqueada'],
            'aguardando_aprovacao': ['concluida'],
            'reprovada': ['em_andamento'],
            'bloqueada': ['em_andamento']
        };

        document.querySelectorAll('.kanban-cards').forEach(function (column) {
            new Sortable(column, {
                group: 'kanban',
                animation: 150,
                onAdd: function (evt) {
                    var card = evt.item;
                    var from = card.getAttribute('data-status');
                    var to = evt.to.getAttribute('data-status');
                    var taskId = card.getAttribute('data-task-id');

                    var revert = function () { window.location.reload(); };

                    if (to === 'reprovada') {
                        showToast('Para reprovar, abra a tarefa e informe o motivo.', true);
                        revert();
                        return;
                    }

                    var allowed = (TRANSITIONS[from] || []).indexOf(to) !== -1;
                    if (!allowed) {
                        showToast('Transição não permitida nesta etapa.', true);
                        revert();
                        return;
                    }

                    var isApprove = from === 'aguardando_aprovacao' && to === 'concluida';
                    var url = isApprove ? '/tarefas/' + taskId + '/aprovar' : '/tarefas/' + taskId + '/status';

                    fetch(url, {
                        method: isApprove ? 'POST' : 'PATCH',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: isApprove ? null : 'status=' + encodeURIComponent(to),
                        credentials: 'same-origin'
                    })
                    .then(function (res) {
                        return res.json().catch(function () { return { ok: false, message: 'Erro inesperado.' }; });
                    })
                    .then(function (data) {
                        if (data.ok) {
                            card.setAttribute('data-status', to);
                            showToast(isApprove ? 'Tarefa aprovada.' : 'Tarefa atualizada.');
                            setTimeout(revert, 700);
                        } else {
                            showToast(data.message || 'Transição não permitida.', true);
                            revert();
                        }
                    })
                    .catch(function () {
                        showToast('Falha de conexão.', true);
                        revert();
                    });
                }
            });
        });
    })();
</script>
@endpush
@endsection
