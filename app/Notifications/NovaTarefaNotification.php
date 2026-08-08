<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NovaTarefaNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Task $task,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'type' => 'nova_tarefa',
            'message' => "Nova tarefa atribuída: {$this->task->title}",
        ];
    }
}
