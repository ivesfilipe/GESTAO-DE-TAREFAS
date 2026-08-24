<?php

namespace App\Listeners;

use App\Events\ComentarioAdicionado;
use App\Events\TarefaAprovada;
use App\Events\TarefaAtribuida;
use App\Events\TarefaCriada;
use App\Events\TarefaReprovada;
use App\Models\User;
use App\Notifications\ComentarioNotification;
use App\Notifications\NovaTarefaNotification;
use App\Notifications\TarefaAprovadaNotification;
use App\Notifications\TarefaReprovadaNotification;

class NotificarPartesInteressadasListener
{
    public function handleTarefaCriada(TarefaCriada $event): void
    {
        $this->notifyAssignee($event->task->assignee, $event->actor, new NovaTarefaNotification($event->task));
    }

    public function handleTarefaAtribuida(TarefaAtribuida $event): void
    {
        $this->notifyAssignee($event->task->assignee, $event->actor, new NovaTarefaNotification($event->task));
    }

    public function handleTarefaAprovada(TarefaAprovada $event): void
    {
        $this->notifyAssignee($event->task->assignee, $event->actor, new TarefaAprovadaNotification($event->task));
    }

    public function handleTarefaReprovada(TarefaReprovada $event): void
    {
        $this->notifyAssignee($event->task->assignee, $event->actor, new TarefaReprovadaNotification($event->task));
    }

    public function handleComentarioAdicionado(ComentarioAdicionado $event): void
    {
        collect([$event->task->creator, $event->task->assignee])
            ->filter()
            ->unique('id')
            ->reject(fn (User $user) => $user->id === $event->actor->id)
            ->filter(fn (User $user) => $user->is_active)
            ->each(fn (User $user) => $user->notify(new ComentarioNotification($event->task)));
    }

    private function notifyAssignee(?User $assignee, User $actor, object $notification): void
    {
        if (! $assignee || ! $assignee->is_active || $assignee->id === $actor->id) {
            return;
        }

        $assignee->notify($notification);
    }
}
