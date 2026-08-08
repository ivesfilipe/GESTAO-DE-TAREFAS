<?php

namespace App\Http\Controllers;

use App\Actions\AddComment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        Gate::authorize('view-task', $task);

        $request->validate([
            'body' => ['required'],
        ]);

        (new AddComment())->execute($task, auth()->user(), $request->body);

        return redirect()->back()->with('success', 'Comentário adicionado.');
    }
}
