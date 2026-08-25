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
    'nao_atribuida' => 'border-slate-300',
    'nova' => 'border-brand-400',
    'recebida' => 'border-indigo-400',
    'em_andamento' => 'border-amber-400',
    'bloqueada' => 'border-red-400',
    'aguardando_aprovacao' => 'border-violet-400',
    'reprovada' => 'border-rose-400',
    'concluida' => 'border-green-400',
]);

$priorityBadges = computed(fn () => [
    'normal' => 'bg-slate-300 text-slate-700',
    'importante' => 'bg-brand-100 text-brand-700',
    'urgente' => 'bg-orange-100 text-orange-700',
    'critica' => 'bg-red-100 text-red-700',
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
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h1 class="text-2xl font-bold text-slate-900">Quadro de Tarefas</h1>
        <div class="flex items-center gap-3">
            @if(Auth::user()->isGestor())
                <select wire:model.live="assignedTo" class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-brand-500 outline-none">
                    <option value="">Toda a equipe</option>
                    @foreach($this->teamMembers as $liderado)
                        <option value="{{ $liderado->id }}">{{ $liderado->name }}</option>
                    @endforeach
                </select>
            @endif
            <a href="{{ route('tasks.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Ver em lista</a>
        </div>
    </div>

    @if($error)
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-3 text-sm text-red-700">
            {{ $error }}
        </div>
    @endif

    <p class="mb-4 text-sm text-slate-500">
        Arraste os cards entre as colunas — apenas transições permitidas para você serão aceitas. O quadro atualiza em tempo real via WebSocket.
    </p>

    <div class="flex gap-4 overflow-x-auto pb-4 items-start">
        @foreach($this->columns as $status => $label)
            <div class="kanban-column shrink-0 w-72 bg-slate-100 rounded-xl border-t-4 {{ $this->columnAccents[$status] }} flex flex-col max-h-[75vh]">
                <div class="flex items-center justify-between px-4 py-3">
                    <h2 class="text-sm font-semibold text-slate-700">{{ $label }}</h2>
                    <span class="rounded-full bg-white px-2 py-0.5 text-xs font-semibold text-slate-500 border border-slate-200"
                          data-column-count="{{ $status }}">
                        {{ ($this->tasksByColumn[$status] ?? collect())->count() }}
                    </span>
                </div>

                <div class="kanban-cards flex-1 overflow-y-auto px-3 pb-3 space-y-2.5 min-h-[80px]" data-status="{{ $status }}">
                    @forelse(($this->tasksByColumn[$status] ?? collect()) as $task)
                        <div wire:key="task-{{ $task->id }}"
                             class="kanban-card bg-white rounded-lg border border-slate-200 p-3 shadow-sm cursor-grab active:cursor-grabbing hover:shadow-md transition-shadow"
                             data-task-id="{{ $task->id }}"
                             data-status="{{ $task->status }}">
                            <div class="flex items-start justify-between gap-2">
                                <a href="{{ route('tasks.show', $task) }}" class="text-sm font-semibold text-slate-900 leading-snug hover:text-brand-600">
                                    {{ $task->title }}
                                    @if($task->isRecurring())<span class="text-violet-600" title="Recorrente">↻</span>@endif
                                </a>
                            </div>
                            <div class="mt-2 flex items-center gap-1.5 flex-wrap">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $this->priorityBadges[$task->priority] }}">
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
                        <p class="rounded-lg border border-dashed border-slate-300 p-3 text-center text-xs text-slate-400">Vazio</p>
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
