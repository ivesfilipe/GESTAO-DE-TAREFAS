<?php

namespace App\Actions;

use App\Events\AlteracaoSolicitada;
use App\Models\ChangeRequest;
use App\Models\Task;
use App\Models\User;

class CreateChangeRequest
{
    public function execute(Task $task, User $requester, string $field, string $currentValue, string $requestedValue, string $justification): ChangeRequest
    {
        if (! in_array($field, ['due_at', 'priority'], true)) {
            throw new \InvalidArgumentException(
                "Campo inválido para solicitação de alteração: '{$field}'"
            );
        }

        $changeRequest = ChangeRequest::create([
            'task_id' => $task->id,
            'requested_by' => $requester->id,
            'field' => $field,
            'current_value' => $currentValue,
            'requested_value' => $requestedValue,
            'justification' => $justification,
            'status' => 'pendente',
        ]);

        AlteracaoSolicitada::dispatch($task, $changeRequest, $requester);

        return $changeRequest;
    }
}
