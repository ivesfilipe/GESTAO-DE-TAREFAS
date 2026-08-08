<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChangeRequest extends Model
{
    protected $fillable = [
        'task_id', 'requested_by', 'field', 'current_value',
        'requested_value', 'justification', 'status',
        'resolved_by', 'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
