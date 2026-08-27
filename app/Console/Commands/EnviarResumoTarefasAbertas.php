<?php

namespace App\Console\Commands;

use App\Mail\OpenTasksDigest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EnviarResumoTarefasAbertas extends Command
{
    protected $signature = 'tarefas:enviar-resumo-tarefas-abertas';

    protected $description = 'Envia um resumo diário de tarefas abertas para cada liderado ativo';

    public function handle(): int
    {
        $count = 0;

        User::query()
            ->where('role', 'liderado')
            ->where('is_active', true)
            ->whereNotNull('email')
            ->chunkById(100, function ($liderados) use (&$count) {
                foreach ($liderados as $liderado) {
                    $tasks = Task::query()
                        ->where('assigned_to', $liderado->id)
                        ->whereNotIn('status', ['concluida', 'cancelada'])
                        ->orderByRaw("CASE priority WHEN 'critica' THEN 1 WHEN 'urgente' THEN 2 WHEN 'importante' THEN 3 ELSE 4 END")
                        ->orderBy('due_at')
                        ->get();

                    if ($tasks->isEmpty()) {
                        continue;
                    }

                    Mail::to($liderado->email)->send(new OpenTasksDigest($liderado, $tasks));
                    $count++;
                }
            });

        $this->info("{$count} resumo(s) de tarefas abertas enviado(s).");

        return self::SUCCESS;
    }
}
