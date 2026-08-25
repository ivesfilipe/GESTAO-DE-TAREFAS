<?php

namespace App\Console\Commands;

use App\Actions\CreateTask;
use App\Models\Task;
use Illuminate\Console\Command;

class GerarTarefasRecorrentes extends Command
{
    protected $signature = 'tarefas:gerar-recorrentes';

    protected $description = 'Gera a próxima instância das tarefas recorrentes cujo prazo de repetição venceu (cadência fixa)';

    public function handle(): int
    {
        $created = 0;

        Task::query()
            ->whereNotNull('recurrence_frequency')
            ->whereNotNull('recurrence_next_at')
            ->where('recurrence_next_at', '<=', now())
            ->with(['assignee', 'creator'])
            ->chunkById(100, function ($tasks) use (&$created) {
                foreach ($tasks as $task) {
                    $interval = Task::recurrenceInterval($task->recurrence_frequency);

                    if ($interval === null || $task->creator === null) {
                        continue;
                    }

                    $generatedDue = $task->recurrence_next_at->copy();
                    $nextDue = $generatedDue->copy()->add($interval);

                    while ($nextDue->lte(now())) {
                        $nextDue = $nextDue->add($interval);
                    }

                    (new CreateTask)->execute($task->creator, [
                        'title' => $task->title,
                        'description' => $task->description,
                        'priority' => $task->priority,
                        'due_at' => $generatedDue->format('Y-m-d H:i:s'),
                        'assigned_to' => $task->assigned_to,
                        'recurrence_frequency' => $task->recurrence_frequency,
                        'recurrence_next_at' => $nextDue->format('Y-m-d H:i:s'),
                        'recurrence_series_id' => $task->recurrence_series_id,
                    ]);

                    $task->forceFill(['recurrence_next_at' => null])->save();

                    $created++;

                    $this->line("Gerada: {$task->title} (vence {$generatedDue->format('d/m/Y')})");
                }
            });

        $this->info("{$created} tarefa(s) recorrente(s) gerada(s).");

        return self::SUCCESS;
    }
}
