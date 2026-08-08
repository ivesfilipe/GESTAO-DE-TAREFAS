<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attachment extends Model
{
    use SoftDeletes;

    public $timestamps = false;
    protected $fillable = ['task_id', 'comment_id', 'uploaded_by', 'file_path', 'file_name', 'file_type', 'file_size', 'created_at'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
