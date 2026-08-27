<?php

use App\Actions\ChangeTaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

use function Livewire\Volt\action;
use function Livewire\Volt\computed;
use function Livewire\Volt\mount;
use function Livewire\Volt\state;

state('assignedTo')->url();

state('error', '');

mount(function ($assignedTo = null) {
    $this->assignedTo = $assignedTo ?? request('assigned_to', '');
});

$columns = computed(fn () => [
    'nao_atribuida' => 'Sem responsável',
    'nova' => 'Nova',
    'recebida' => 'Recebida',
    'em_andamento' => 'Em andamento',
    'bloqueada' => 'Bloqueada',
    'aguardando_aprovacao' => 'Aguardando aprovação',
    'reprovada' => 'Reprovada',
    'concluida' => 'Concluída',
]);

$columnAccents = computed(fn () => [
    'nao_atribuida' => 'border-slate-300 dark:border-slate-600',
    'nova' => 'border-brand-400 dark:border-brand-500',
    'recebida' => 'border-indigo-400 dark:border-indigo-500',
    'em_andamento' => 'border-amber-400 dark:border-amber-500',
    'bloqueada' => 'border-red-400 dark:border-red-500',
    'aguardando_aprovacao' => 'border-violet-400 dark:border-violet-500',
    'reprovada' => 'border-rose-400 dark:border-rose-500',
    'concluida' => 'border-emerald-400 dark:border-emerald-500',
]);

$priorityBadges = computed(fn () => [
    'normal' => 'bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-white/10',
    'importante' => 'bg-brand-50 dark:bg-brand-500/15 text-brand-700 dark:text-brand-300 border border-brand-200 dark:border-brand-500/20',
    'urgente' => 'bg-orange-50 dark:bg-orange-500/15 text-orange-700 dark:text-orange-300 border border-orange-200 dark:border-orange-500/20',
    'critica' => 'bg-red-50 dark:bg-red-500/15 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-500/20',
]);

$tasksByColumn = computed(function () {
    $user = Auth::user();

    $base = Task::query()
        ->whereNotIn('status', ['cancelada'])
        ->with(['assignee'])
        ->when($this->assignedTo !== '' && $this->assignedTo !== null, fn ($q) => $q->where('assigned_to', $this->assignedTo));

    if (! $user->isGestor()) {
        $base->where('assigned_to', $user->id);
    }

    return $base->orderBy('due_at')
        ->get()
        ->groupBy('status');
});

$teamMembers = computed(fn () => User::where('role', 'liderado')->where('is_active', true)->orderBy('name')->get());

$moveTask = action(function ($taskId, $newStatus) {
    $task = Task::find($taskId);
    $user = Auth::user();

    if (! $task || ! $newStatus) {
        return;
    }

    if ($task->status === $newStatus) {
        return;
    }

    if ($newStatus === 'cancelada' && ! $user->isGestor()) {
        $this->error = 'Apenas o gestor pode cancelar tarefas.';

        return;
    }

    if ((int) $task->assigned_to !== (int) $user->id && $newStatus !== 'cancelada') {
        $this->error = 'Apenas o responsável pode mover esta tarefa.';

        return;
    }

    try {
        ChangeTaskStatus::change($task, $user, $newStatus);
        $this->error = '';
    } catch (\InvalidArgumentException $e) {
        $this->error = 'Movimento não permitido para esta tarefa.';
    }
});
?>

<div class="mx-auto max-w-[1800px] px-4 sm:px-6 lg:px-8 py-6" x-data>
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 animate-fade-in-up">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Quadro de Tarefas</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Arraste os cards · atualização em tempo real</p>
        </div>
        <div class="flex items-center gap-3">
            @if(Auth::user()->isGestor())
                <select wire:model.live="assignedTo" class="rounded-xl border border-slate-200/60 dark:border-white/10 bg-white/80 dark:bg-white/[0.06] backdrop-blur px-3 py-2 text-sm text-slate-900 dark:text-white focus:border-brand-400 focus:ring-2 focus:ring-brand-500/15 outline-none shadow-sm">
                    <option value="">Toda a equipe</option>
                    @foreach($this->teamMembers as $liderado)
                        <option value="{{ $liderado->id }}">{{ $liderado->name }}</option>
                    @endforeach
                </select>
            @endif
            <a href="{{ route('tasks.index') }}" class="inline-flex items-center gap-1 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/[0.06] backdrop-blur px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/10 hover:shadow-sm transition-all">Ver em lista <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></a>
        </div>
    </div>

    @if($error)
        <div class="mb-4 rounded-xl bg-red-500/10 dark:bg-red-500/15 border border-red-200 dark:border-red-500/20 p-3 text-sm text-red-700 dark:text-red-300">
            {{ $error }}
        </div>
    @endif

    <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">
        Arraste os cards entre as colunas — apenas transições permitidas para você serão aceitas. O quadro atualiza em tempo real via WebSocket.
    </p>

    <div class="flex gap-4 overflow-x-auto pb-4 items-start scrollbar-thin">
        @foreach($this->columns as $status => $label)
            <div class="kanban-column group/column relative shrink-0 w-[296px] rounded-[18px] border border-slate-200/60 dark:border-white/10 bg-white/70 dark:bg-white/[0.045] backdrop-blur-xl flex flex-col max-h-[75vh] shadow-sm dark:shadow-none overflow-hidden hover:shadow-md dark:hover:bg-white/[0.06] transition-all border-t-[3px] {{ $this->columnAccents[$status] }}">
                {{-- colored top glow --}}
                <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/20 dark:via-white/15 to-transparent opacity-0 group-hover/column:opacity-100 transition-opacity"></div>
                <div class="flex items-center justify-between px-4 py-3.5 bg-gradient-to-b from-white/60 dark:from-white/[0.04] to-transparent border-b border-slate-200/40 dark:border-white/5">
                    <h2 class="text-[13px] font-semibold tracking-wide text-slate-700 dark:text-slate-200">{{ $label }}</h2>
                    <span class="inline-flex items-center justify-center min-w-6 rounded-full bg-white dark:bg-white/10 px-2 py-0.5 text-xs font-bold text-slate-500 dark:text-slate-300 border border-slate-200 dark:border-white/10 shadow-sm"
                          data-column-count="{{ $status }}">
                        {{ ($this->tasksByColumn[$status] ?? collect())->count() }}
                    </span>
                </div>

                <div class="kanban-cards flex-1 overflow-y-auto px-3 pb-3 pt-3 space-y-2.5 min-h-[80px] bg-slate-50/40 dark:bg-transparent" data-status="{{ $status }}">
                    @forelse(($this->tasksByColumn[$status] ?? collect()) as $task)
                        <div wire:key="task-{{ $task->id }}"
                             class="kanban-card group/card relative overflow-hidden bg-white dark:bg-slate-800/60 backdrop-blur rounded-xl border border-slate-200/70 dark:border-white/10 p-3.5 shadow-sm dark:shadow-none cursor-grab active:cursor-grabbing hover:shadow-lg dark:hover:shadow-black/20 hover:-translate-y-0.5 hover:border-slate-300 dark:hover:border-white/20 hover:bg-white dark:hover:bg-slate-800/80 transition-all"
                             data-task-id="{{ $task->id }}"
                             data-status="{{ $task->status }}">
                            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-slate-200/50 dark:via-white/10 to-transparent opacity-60"></div>
                            <div class="flex items-start justify-between gap-2">
                                <a href="{{ route('tasks.show', $task) }}" class="text-[13.5px] font-semibold text-slate-900 dark:text-white leading-snug hover:text-brand-600 dark:hover:text-brand-300 line-clamp-2">
                                    {{ $task->title }}
                                    @if($task->isRecurring())<span class="text-violet-600 dark:text-violet-400" title="Recorrente">↻</span>@endif
                                </a>
                            </div>
                            <div class="mt-2.5 flex items-center gap-1.5 flex-wrap">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-semibold border {{ $this->priorityBadges[$task->priority] }}">
                                    {{ ucfirst($task->priority) }}
                                </span>
                                @if($task->isOverdue())
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[10px] font-bold bg-red-500 dark:bg-red-500/20 text-white dark:text-red-300 border border-red-500 dark:border-red-500/30">Atrasada</span>
                                @endif
                            </div>
                            <div class="mt-2.5 flex items-center justify-between text-xs">
                                <span class="truncate pr-2 text-slate-500 dark:text-slate-400">{{ $task->assignee?->name ?? 'Sem responsável' }}</span>
                                <span class="shrink-0 inline-flex items-center gap-1 rounded-full bg-slate-50 dark:bg-white/5 border border-slate-200/60 dark:border-white/10 px-2 py-0.5 text-[11px] {{ $task->isOverdue() ? 'text-red-600 dark:text-red-400 border-red-200 dark:border-red-500/30 font-medium' : 'text-slate-500 dark:text-slate-400' }}">
                                    {{ $task->due_at?->format('d/m') ?? '' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-300/70 dark:border-white/10 bg-white/50 dark:bg-white/[0.02] p-6 text-center">
                            <p class="text-xs font-medium text-slate-400 dark:text-slate-500">Vazio</p>
                            <p class="mt-1 text-[11px] text-slate-400/70 dark:text-slate-500/60">Arraste um card para cá</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>

@script
<script>
    import Sortable from 'sortablejs';

    document.querySelectorAll('.kanban-cards').forEach((column) => {
        new Sortable(column, {
            group: 'kanban',
            animation: 150,
            ghostClass: 'opacity-40',
            dragClass: 'shadow-xl',
            onEnd(evt) {
                const taskId = evt.item.dataset.taskId;
                const targetStatus = evt.to.dataset.status;
                if (taskId && targetStatus) {
                    $wire.moveTask(taskId, targetStatus);
                }
            },
        });
    });

    document.addEventListener('task:updated', () => $wire.$refresh());
</script>
@endscript
