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
$rejectionCategoryLabels = [
    'nao_atende' => 'Não atende aos requisitos',
    'escopo_mudou' => 'Escopo mudou',
    'info_incompleta' => 'Informações incompletas',
    'outro' => 'Outro',
];
$isGestor = Auth::user()->isGestor();
$isAssignee = Auth::id() === $task->assigned_to;
@endphp

@section('title', $task->title . ' - Gestão de Tarefas')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('tasks.index') }}" class="text-sm text-slate-500 hover:text-slate-700">&larr; Tarefas</a>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $task->title }}</h1>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ $priorityBadges[$task->priority] }}">
                {{ ucfirst($task->priority) }}
            </span>
            <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ $statusBadges[$task->status] }}">
                {{ $statusLabels[$task->status] }}
            </span>
        </div>
    </div>

    @if($task->isOverdue())
        <div class="mb-6 rounded-lg bg-red-50 border border-red-200 px-4 py-3 flex items-center gap-2">
            <svg class="size-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-sm font-medium text-red-700">
                Atrasada
            </span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
                <h2 class="text-sm font-semibold text-slate-900 mb-4">Detalhes</h2>

                @if($task->description)
                    <div class="mb-4 text-sm text-slate-700 whitespace-pre-wrap">{{ $task->description }}</div>
                @else
                    <p class="mb-4 text-sm text-slate-400 italic">Sem descrição.</p>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-slate-500">Criado por:</span>
                        <span class="ml-1 text-slate-900 font-medium">{{ $task->creator?->name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500">Responsável:</span>
                        <span class="ml-1 text-slate-900 font-medium">{{ $task->assignee?->name ?? 'Não atribuído' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500">Prazo:</span>
                        <span class="ml-1 text-slate-900 font-medium {{ $task->isOverdue() ? 'text-red-600' : '' }}">
                            {{ $task->due_at?->format('d/m/Y H:i') ?? '-' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-500">Criado em:</span>
                        <span class="ml-1 text-slate-900 font-medium">{{ $task->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($task->block_reason)
                        <div class="sm:col-span-2">
                            <span class="text-slate-500">Motivo do bloqueio:</span>
                            <p class="mt-1 text-slate-700">{{ $task->block_reason }}</p>
                        </div>
                    @endif
                    @if($task->rejection_note)
                        <div class="sm:col-span-2">
                            <span class="text-slate-500">Motivo da reprovação:</span>
                            <p class="mt-1 text-slate-700">{{ $task->rejection_note }}</p>
                            @if($task->rejection_category)
                                <span class="mt-1 inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">
                                    {{ $rejectionCategoryLabels[$task->rejection_category] ?? $task->rejection_category }}
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
                <h2 class="text-sm font-semibold text-slate-900 mb-4">Comentários</h2>

                <div class="space-y-4 mb-6">
                    @forelse($task->comments as $comment)
                        <div class="flex gap-3">
                            <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand-600 text-xs font-bold">
                                {{ substr($comment->author?->name ?? '?', 0, 2) }}
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-slate-900">{{ $comment->author?->name ?? 'Desconhecido' }}</span>
                                    <span class="text-xs text-slate-400">{{ $comment->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <p class="mt-1 text-sm text-slate-700 whitespace-pre-wrap">{{ $comment->body }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400 italic">Nenhum comentário ainda.</p>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('tasks.comments.store', $task) }}" enctype="multipart/form-data">
                    @csrf
                    <div>
                        <textarea
                            name="body"
                            rows="3"
                            required
                            class="block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none resize-none"
                            placeholder="Adicione um comentário..."
                        ></textarea>
                    </div>
                    <div class="mt-2 flex items-center gap-3">
                        <label class="flex items-center gap-2 text-sm text-slate-500">
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                            <input type="file" name="file" accept=".jpg,.jpeg,.png,.gif,.pdf" class="text-sm text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100" />
                        </label>
                        <div class="ml-auto">
                            <button type="submit" class="rounded-lg bg-brand-700 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
                                Enviar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
                <h2 class="text-sm font-semibold text-slate-900 mb-4">Anexos</h2>

                <div class="space-y-2 mb-6">
                    @forelse($task->attachments as $attachment)
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 px-4 py-3">
                            <div class="flex items-center gap-3">
                                <svg class="size-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-slate-900">{{ $attachment->file_name }}</p>
                                    <p class="text-xs text-slate-400">{{ $attachment->file_type }} &middot; {{ number_format($attachment->file_size / 1024, 1) }} KB</p>
                                </div>
                            </div>
                             <a href="{{ route('tasks.attachments.download', [$task, $attachment]) }}" class="text-sm font-medium text-brand-500 hover:text-brand-600" download>
                                Download
                            </a>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400 italic">Nenhum anexo.</p>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('tasks.attachments.store', $task) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="flex items-center gap-3">
                        <input
                            type="file"
                            name="file"
                            required
                            class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100"
                        />
                        <button type="submit" class="shrink-0 rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200 transition-colors">
                            Anexar
                        </button>
                    </div>
                </form>
            </div>

            <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
                <h2 class="text-sm font-semibold text-slate-900 mb-4">Histórico</h2>

                <div class="relative">
                    <div class="absolute left-[11px] top-0 bottom-0 w-px bg-slate-200"></div>
                    <div class="space-y-4">
                        @forelse($task->historyEvents as $event)
                            <div class="relative flex gap-4 pl-8">
                                <div class="absolute left-0 flex size-6 items-center justify-center rounded-full border-2 border-white bg-slate-100">
                                    <svg class="size-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-slate-700">
                                        <span class="font-medium">{{ $event->actor?->name ?? 'Sistema' }}</span>
                                        {{ $event->event_type }}
                                    </p>
                                    @if($event->payload)
                                        <p class="mt-0.5 text-xs text-slate-400">{{ json_encode($event->payload) }}</p>
                                    @endif
                                    <p class="mt-0.5 text-xs text-slate-400">{{ $event->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-400 italic">Nenhum evento registrado.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm sticky top-20">
                <h2 class="text-sm font-semibold text-slate-900 mb-4">Ações</h2>

                <div class="space-y-2">
                    @if($isGestor)
                        @if($task->status === 'nao_atribuida')
                            <button
                                type="button"
                                onclick="document.getElementById('modal-assign').classList.remove('hidden')"
                                class="w-full rounded-lg bg-brand-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 transition-colors"
                            >
                                Atribuir
                            </button>
                        @endif

                        @if(in_array($task->status, ['aguardando_aprovacao']))
                            <form method="POST" action="{{ route('tasks.approve', $task) }}">
                                @csrf
                                <button type="submit" class="w-full rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-500 transition-colors">
                                    Aprovar
                                </button>
                            </form>
                            <button
                                type="button"
                                onclick="document.getElementById('modal-reject').classList.remove('hidden')"
                                class="w-full rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-500 transition-colors"
                            >
                                Reprovar
                            </button>
                        @endif

                        @if($task->status === 'bloqueada')
                            <form method="POST" action="{{ route('tasks.unblock', $task) }}">
                                @csrf
                                <button type="submit" class="w-full rounded-lg bg-brand-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
                                    Desbloquear
                                </button>
                            </form>

                            <form method="POST" action="{{ route('tasks.change-status', $task) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="cancelada">
                                <button type="submit" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors" onclick="return confirm('Tem certeza que deseja cancelar esta tarefa?')">
                                    Cancelar tarefa
                                </button>
                            </form>
                        @endif
                    @endif

                    @if($isAssignee && !in_array($task->status, ['concluida', 'cancelada', 'bloqueada', 'reprovada', 'nao_atribuida']))
                        @if($task->status === 'nova')
                            <form method="POST" action="{{ route('tasks.change-status', $task) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="recebida">
                                <button type="submit" class="w-full rounded-lg bg-brand-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
                                    Receber tarefa
                                </button>
                            </form>
                        @endif

                        @if($task->status === 'recebida')
                            <form method="POST" action="{{ route('tasks.change-status', $task) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="em_andamento">
                                <button type="submit" class="w-full rounded-lg bg-brand-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
                                    Iniciar
                                </button>
                            </form>
                        @endif

                        @if($task->status === 'em_andamento')
                            <form method="POST" action="{{ route('tasks.change-status', $task) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="aguardando_aprovacao">
                                <button type="submit" class="w-full rounded-lg bg-purple-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-purple-500 transition-colors">
                                    Enviar para aprovação
                                </button>
                            </form>
                            <button
                                type="button"
                                onclick="document.getElementById('modal-block').classList.remove('hidden')"
                                class="w-full rounded-lg bg-yellow-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-yellow-500 transition-colors"
                            >
                                Bloquear
                            </button>
                        @endif

                        @if($task->status !== 'aguardando_aprovacao')
                            <button
                                type="button"
                                onclick="document.getElementById('modal-change-request').classList.remove('hidden')"
                                class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors"
                            >
                                Solicitar alteração
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal-assign" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 px-4">
    <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Atribuir tarefa</h3>
        <form method="POST" action="{{ route('tasks.assign', $task) }}">
            @csrf
            @method('PATCH')
            <div class="mb-4">
                <label for="assignee_id" class="block text-sm font-medium text-slate-700">Responsável</label>
                <select name="assigned_to" id="assignee_id" required class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none">
                    <option value="">Selecione...</option>
                    @foreach($liderados ?? [] as $liderado)
                        <option value="{{ $liderado->id }}">{{ $liderado->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('modal-assign').classList.add('hidden')" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancelar
                </button>
                <button type="submit" class="rounded-lg bg-brand-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">
                    Atribuir
                </button>
            </div>
        </form>
    </div>
</div>

<div id="modal-reject" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 px-4">
    <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Reprovar tarefa</h3>
        <form method="POST" action="{{ route('tasks.reject', $task) }}">
            @csrf
            <div class="mb-4">
                <label for="rejection_category" class="block text-sm font-medium text-slate-700">Categoria</label>
                <select name="rejection_category" id="rejection_category" required class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none">
                    <option value="">Selecione...</option>
                    @foreach($rejectionCategoryLabels as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label for="rejection_note" class="block text-sm font-medium text-slate-700">Motivo</label>
                <textarea name="rejection_note" id="rejection_note" rows="3" required class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none resize-none" placeholder="Descreva o motivo da reprovação..."></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('modal-reject').classList.add('hidden')" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancelar
                </button>
                <button type="submit" class="rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-500">
                    Reprovar
                </button>
            </div>
        </form>
    </div>
</div>

<div id="modal-block" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 px-4">
    <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Bloquear tarefa</h3>
        <form method="POST" action="{{ route('tasks.block', $task) }}">
            @csrf
            <div class="mb-4">
                <label for="block_reason" class="block text-sm font-medium text-slate-700">Motivo</label>
                <textarea name="block_reason" id="block_reason" rows="3" required class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none resize-none" placeholder="Descreva o motivo do bloqueio..."></textarea>
            </div>
            <div class="mb-4">
                <label for="blocked_on" class="block text-sm font-medium text-slate-700">De quem depende</label>
                <input type="text" name="blocked_on" id="blocked_on" class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none" placeholder="Nome da pessoa ou equipe" />
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('modal-block').classList.add('hidden')" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancelar
                </button>
                <button type="submit" class="rounded-lg bg-yellow-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-yellow-500">
                    Bloquear
                </button>
            </div>
        </form>
    </div>
</div>

<div id="modal-change-request" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 px-4">
    <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Solicitar alteração</h3>
        <form method="POST" action="{{ route('tasks.request-change', $task) }}">
            @csrf
            <div class="mb-4">
                <label for="change_field" class="block text-sm font-medium text-slate-700">Campo</label>
                <select name="field" id="change_field" required class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none">
                    <option value="due_at">Prazo</option>
                    <option value="priority">Prioridade</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700">Valor atual</label>
                <input type="text" readonly id="current_value_display" class="mt-1 block w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-500" />
                <input type="hidden" name="current_value" id="current_value_hidden" />
            </div>
            <div class="mb-4">
                <label for="requested_value" class="block text-sm font-medium text-slate-700">Valor solicitado</label>
                <input type="text" name="requested_value" id="requested_value" required class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none" placeholder="Novo valor..." />
            </div>
            <div class="mb-4">
                <label for="justification" class="block text-sm font-medium text-slate-700">Justificativa</label>
                <textarea name="justification" id="justification" rows="3" required class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none resize-none" placeholder="Justifique a alteração..."></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('modal-change-request').classList.add('hidden')" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancelar
                </button>
                <button type="submit" class="rounded-lg bg-brand-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">
                    Solicitar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('change_field')?.addEventListener('change', function() {
        const field = this.value;
        const currentValueDisplay = document.getElementById('current_value_display');
        const currentValueHidden = document.getElementById('current_value_hidden');
        if (field === 'due_at') {
            currentValueDisplay.value = '{{ $task->due_at?->format("d/m/Y H:i") ?? "Não definido" }}';
            currentValueHidden.value = '{{ $task->due_at?->format("Y-m-d H:i") ?? "" }}';
        } else if (field === 'priority') {
            currentValueDisplay.value = '{{ ucfirst($task->priority) }}';
            currentValueHidden.value = '{{ $task->priority }}';
        }
    });

    document.getElementById('change_field')?.dispatchEvent(new Event('change'));
</script>
@endpush
