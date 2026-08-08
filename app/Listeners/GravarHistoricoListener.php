<?php

namespace App\Listeners;

use App\Actions\RecordHistory;
use App\Events\AlteracaoSolicitada;
use App\Events\AnexoAdicionado;
use App\Events\ComentarioAdicionado;
use App\Events\ConclusaoSolicitada;
use App\Events\PrazoAlterado;
use App\Events\PrioridadeAlterada;
use App\Events\StatusAlterado;
use App\Events\TarefaAprovada;
use App\Events\TarefaAtribuida;
use App\Events\TarefaBloqueada;
use App\Events\TarefaCancelada;
use App\Events\TarefaCriada;
use App\Events\TarefaDesbloqueada;
use App\Events\TarefaReprovada;

class GravarHistoricoListener
{
    public function handleTarefaCriada(TarefaCriada $event): void
    {
        RecordHistory::record($event->task, $event->actor, 'created', [
            'title' => $event->task->title,
            'actor_name' => $event->actor->name,
        ]);
    }

    public function handleTarefaAtribuida(TarefaAtribuida $event): void
    {
        RecordHistory::record($event->task, $event->actor, 'assigned', [
            'previous_assignee' => $event->previousAssignee?->name,
            'new_assignee' => $event->task->assignee?->name,
            'actor_name' => $event->actor->name,
        ]);
    }

    public function handlePrioridadeAlterada(PrioridadeAlterada $event): void
    {
        RecordHistory::record($event->task, $event->actor, 'priority_changed', [
            'old' => $event->oldPriority,
            'new' => $event->newPriority,
            'actor_name' => $event->actor->name,
        ]);
    }

    public function handlePrazoAlterado(PrazoAlterado $event): void
    {
        RecordHistory::record($event->task, $event->actor, 'due_date_changed', [
            'old' => $event->oldDueAt,
            'new' => $event->newDueAt,
            'actor_name' => $event->actor->name,
        ]);
    }

    public function handleStatusAlterado(StatusAlterado $event): void
    {
        RecordHistory::record($event->task, $event->actor, 'status_changed', [
            'old' => $event->oldStatus,
            'new' => $event->newStatus,
            'actor_name' => $event->actor->name,
        ]);
    }

    public function handleComentarioAdicionado(ComentarioAdicionado $event): void
    {
        RecordHistory::record($event->task, $event->actor, 'comment_added', [
            'comment_id' => $event->comment->id,
            'actor_name' => $event->actor->name,
        ]);
    }

    public function handleAnexoAdicionado(AnexoAdicionado $event): void
    {
        RecordHistory::record($event->task, $event->actor, 'attachment_added', [
            'attachment_id' => $event->attachment->id,
            'file_name' => $event->attachment->file_name,
            'actor_name' => $event->actor->name,
        ]);
    }

    public function handleTarefaBloqueada(TarefaBloqueada $event): void
    {
        RecordHistory::record($event->task, $event->actor, 'blocked', [
            'actor_name' => $event->actor->name,
        ]);
    }

    public function handleTarefaDesbloqueada(TarefaDesbloqueada $event): void
    {
        RecordHistory::record($event->task, $event->actor, 'unblocked', [
            'actor_name' => $event->actor->name,
        ]);
    }

    public function handleConclusaoSolicitada(ConclusaoSolicitada $event): void
    {
        RecordHistory::record($event->task, $event->actor, 'status_changed', [
            'old' => $event->task->getOriginal('status'),
            'new' => 'aguardando_aprovacao',
            'actor_name' => $event->actor->name,
        ]);
    }

    public function handleTarefaAprovada(TarefaAprovada $event): void
    {
        RecordHistory::record($event->task, $event->actor, 'approved', [
            'actor_name' => $event->actor->name,
        ]);
    }

    public function handleTarefaReprovada(TarefaReprovada $event): void
    {
        RecordHistory::record($event->task, $event->actor, 'rejected', [
            'actor_name' => $event->actor->name,
        ]);
    }

    public function handleTarefaCancelada(TarefaCancelada $event): void
    {
        RecordHistory::record($event->task, $event->actor, 'cancelled', [
            'actor_name' => $event->actor->name,
        ]);
    }

    public function handleAlteracaoSolicitada(AlteracaoSolicitada $event): void
    {
        RecordHistory::record($event->task, $event->actor, 'change_requested', [
            'change_request_id' => $event->changeRequest->id,
            'field' => $event->changeRequest->field,
            'current_value' => $event->changeRequest->current_value,
            'requested_value' => $event->changeRequest->requested_value,
            'actor_name' => $event->actor->name,
        ]);
    }
}
