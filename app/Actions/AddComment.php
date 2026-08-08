<?php

namespace App\Actions;

use App\Events\ComentarioAdicionado;
use App\Models\Comment;
use App\Models\Task;
use App\Models\User;

class AddComment
{
    public function execute(Task $task, User $author, string $body): Comment
    {
        $comment = Comment::create([
            'task_id' => $task->id,
            'author_id' => $author->id,
            'body' => $body,
            'created_at' => now(),
        ]);

        ComentarioAdicionado::dispatch($task, $comment, $author);

        return $comment;
    }
}
