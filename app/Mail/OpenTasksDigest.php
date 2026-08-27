<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Collection;

class OpenTasksDigest extends Mailable implements ShouldQueue
{
    use Queueable;

    /**
     * @param  Collection<int, Task>  $tasks
     */
    public function __construct(public User $user, public Collection $tasks) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Suas tarefas abertas - Gestão de Tarefas',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.open-tasks-digest',
        );
    }
}
