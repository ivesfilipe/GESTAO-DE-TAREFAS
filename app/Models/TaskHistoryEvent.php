<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskHistoryEvent extends Model
{
    public $timestamps = false;

    protected $fillable = ['task_id', 'actor_id', 'event_type', 'payload', 'created_at'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'payload' => 'array',
        ];
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
