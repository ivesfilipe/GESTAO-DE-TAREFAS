<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\PrazoProximoNotification;
use Illuminate\Console\Command;

class NotificarPrazosProximos extends Command
{
    protected $signature = 'tarefas:notificar-prazos-proximos';

    protected $description = 'Notifica responsáveis sobre tarefas ativas que vencem nas próximas 24 horas';

    public function handle(): int
    {
        $count = 0;

        Task::query()
            ->whereNotNull('due_at')
            ->whereNotNull('assigned_to')
            ->whereBetween('due_at', [now(), now()->addDay()])
            ->whereNotIn('status', ['concluida', 'cancelada', 'bloqueada'])
            ->with('assignee')
            ->chunkById(100, function ($tasks) use (&$count) {
                foreach ($tasks as $task) {
                    if (! $task->assignee?->is_active) {
                        continue;
                    }

                    $task->assignee->notify(new PrazoProximoNotification($task));
                    $count++;
                }
            });

        $this->info("{$count} notificação(ões) de prazo próximo enviada(s).");

        return self::SUCCESS;
    }
}
