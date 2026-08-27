<?php

namespace App\Listeners;

use App\Events\TarefaAtribuida;
use App\Events\TarefaCriada;
use App\Mail\TaskAssignedMail;
use App\Models\Task;
use Illuminate\Support\Facades\Mail;

class EnviarEmailDeAtribuicaoListener
{
    public function handleTarefaCriada(TarefaCriada $event): void
    {
        $task = $event->task->fresh();

        if (! $task || ! $task->assigned_to) {
            return;
        }

        $this->send($task);
    }

    public function handleTarefaAtribuida(TarefaAtribuida $event): void
    {
        $this->send($event->task);
    }

    private function send(Task $task): void
    {
        $assignee = $task->assignee;

        if (! $assignee || ! $assignee->is_active || ! $assignee->email) {
            return;
        }

        Mail::to($assignee->email)->send(new TaskAssignedMail($assignee, $task));
    }
}
