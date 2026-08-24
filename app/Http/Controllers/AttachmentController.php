<?php

namespace App\Http\Controllers;

use App\Actions\UploadAttachment;
use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        Gate::authorize('view-task', $task);

        $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,pdf', 'max:10240'],
        ]);

        $comment = null;
        if ($request->filled('comment_id')) {
            $comment = Comment::find($request->comment_id);
        }

        (new UploadAttachment)->execute($task, auth()->user(), $request->file('file'), $comment);

        return redirect()->back()->with('success', 'Anexo enviado.');
    }

    public function download(Task $task, Attachment $attachment)
    {
        Gate::authorize('view-task', $task);

        abort_unless($attachment->task_id === $task->id, 404);

        return Storage::disk('anexos')->download($attachment->file_path, $attachment->file_name);
    }
}
