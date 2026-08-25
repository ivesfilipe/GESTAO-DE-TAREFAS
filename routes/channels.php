<?php

use App\Broadcasting\TaskChannel;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('task.{taskId}', [TaskChannel::class, 'join']);
