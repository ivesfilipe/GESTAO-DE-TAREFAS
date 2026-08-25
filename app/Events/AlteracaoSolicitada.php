<?php

namespace App\Events;

use App\Events\Concerns\BroadcastsTaskActivity;
use App\Models\ChangeRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AlteracaoSolicitada implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use BroadcastsTaskActivity,
        Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Task $task,
        public ChangeRequest $changeRequest,
        public User $actor,
    ) {}
}
