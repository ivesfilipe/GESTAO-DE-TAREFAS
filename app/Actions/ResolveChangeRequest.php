<?php

namespace App\Actions;

use App\Events\PrazoAlterado;
use App\Events\PrioridadeAlterada;
use App\Models\ChangeRequest;
use App\Models\User;

class ResolveChangeRequest
{
    public function execute(ChangeRequest $changeRequest, User $resolver, string $status): ChangeRequest
    {
        $changeRequest->update([
            'resolved_by' => $resolver->id,
            'resolved_at' => now(),
            'status' => $status,
        ]);

        if ($status === 'aprovada') {
            $task = $changeRequest->task;

            if ($changeRequest->field === 'due_at') {
                $oldDueAt = $task->due_at;
                $task->update(['due_at' => $changeRequest->requested_value]);

                PrazoAlterado::dispatch($task, $resolver, $oldDueAt, $changeRequest->requested_value);
            }

            if ($changeRequest->field === 'priority') {
                $oldPriority = $task->priority;
                $task->update(['priority' => $changeRequest->requested_value]);

                PrioridadeAlterada::dispatch($task, $resolver, $oldPriority, $changeRequest->requested_value);
            }
        }

        return $changeRequest->refresh();
    }
}
