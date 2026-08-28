<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIUsageLog extends Model
{
    use HasFactory;

    protected $table = 'ai_usage_logs';

    protected $fillable = [
        'user_id',
        'provider',
        'model',
        'prompt',
        'response',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'status',
        'error_message',
        'duration_ms',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'duration_ms' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
