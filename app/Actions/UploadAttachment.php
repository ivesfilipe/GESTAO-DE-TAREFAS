<?php

namespace App\Actions;

use App\Events\AnexoAdicionado;
use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class UploadAttachment
{
    public function execute(Task $task, User $uploader, UploadedFile $file, ?Comment $comment): Attachment
    {
        $validMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf'];
        $maxSize = 10485760;

        if (!in_array($file->getMimeType(), $validMimeTypes, true)) {
            throw new \InvalidArgumentException(
                'Tipo de arquivo não permitido. Use: jpg, jpeg, png, gif ou pdf.'
            );
        }

        if ($file->getSize() > $maxSize) {
            throw new \InvalidArgumentException(
                'O arquivo excede o tamanho máximo de 10MB.'
            );
        }

        $path = $file->store('attachments', 'public');

        $attachment = Attachment::create([
            'task_id' => $task->id,
            'comment_id' => $comment?->id,
            'uploaded_by' => $uploader->id,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'created_at' => now(),
        ]);

        AnexoAdicionado::dispatch($task, $attachment, $uploader);

        return $attachment;
    }
}
