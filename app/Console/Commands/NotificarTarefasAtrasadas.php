<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\TarefaAtrasadaNotification;
use Illuminate\Console\Command;

class NotificarTarefasAtrasadas extends Command
{
    protected $signature = 'tarefas:notificar-atrasadas';

    protected $description = 'Notifica responsáveis sobre tarefas ativas atrasadas (lembrete diário, respeita o fuso de cada responsável)';

    public function handle(): int
    {
        $count = 0;

        Task::query()
            ->whereNotNull('due_at')
            ->whereNotNull('assigned_to')
            ->whereNotIn('status', ['concluida', 'cancelada', 'bloqueada'])
            ->with('assignee')
            ->chunkById(100, function ($tasks) use (&$count) {
                foreach ($tasks as $task) {
                    if (! $task->assignee?->is_active) {
                        continue;
                    }

                    if (! $task->isOverdue()) {
                        continue;
                    }

                    $task->assignee->notify(new TarefaAtrasadaNotification($task));
                    $count++;
                }
            });

        $this->info("{$count} notificação(ões) de tarefa atrasada enviada(s).");

        return self::SUCCESS;
    }
}
