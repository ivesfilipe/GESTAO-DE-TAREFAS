<?php

namespace App\Actions;

use App\Events\StatusAlterado;
use App\Models\Task;
use App\Models\User;

class ChangeTaskStatus
{
    private const TRANSITIONS = [
        'nao_atribuida' => ['nova'],
        'nova' => ['recebida'],
        'recebida' => ['em_andamento'],
        'em_andamento' => ['aguardando_aprovacao', 'bloqueada'],
        'aguardando_aprovacao' => ['concluida', 'reprovada'],
        'reprovada' => ['em_andamento'],
        'bloqueada' => ['em_andamento'],
    ];

    public static function change(Task $task, User $actor, string $newStatus, array $extra = []): Task
    {
        $oldStatus = $task->status;

        if ($newStatus !== 'cancelada') {
            $allowed = self::TRANSITIONS[$oldStatus] ?? [];
            if (! in_array($newStatus, $allowed, true)) {
                throw new \InvalidArgumentException(
                    "Transição inválida: '{$oldStatus}' -> '{$newStatus}'"
                );
            }
        }

        $data = ['status' => $newStatus];

        if ($newStatus === 'concluida') {
            $data['completed_at'] = now();
        }

        if (isset($extra['approved_by'])) {
            $data['approved_by'] = $extra['approved_by'];
        }

        if (isset($extra['rejection_category'])) {
            $data['rejection_category'] = $extra['rejection_category'];
        }

        if (isset($extra['rejection_note'])) {
            $data['rejection_note'] = $extra['rejection_note'];
        }

        if ($newStatus === 'bloqueada') {
            if (isset($extra['block_reason'])) {
                $data['block_reason'] = $extra['block_reason'];
            }
            if (isset($extra['blocked_on'])) {
                $data['blocked_on'] = $extra['blocked_on'];
            }
        }

        $task->update($data);

        if ($newStatus === 'cancelada') {
            $task->delete();
        }

        StatusAlterado::dispatch($task, $actor, $oldStatus, $newStatus);

        return $task;
    }
}
