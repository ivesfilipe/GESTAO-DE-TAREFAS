<?php

namespace App\Http\Controllers;

use App\Actions\AddComment;
use App\Actions\UploadAttachment;
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
            'file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,pdf', 'max:10240'],
        ]);

        $comment = (new AddComment)->execute($task, auth()->user(), $request->body);

        if ($request->hasFile('file')) {
            (new UploadAttachment)->execute($task, auth()->user(), $request->file('file'), $comment);
        }

        return redirect()->back()->with('success', 'Comentário adicionado.');
    }
}
