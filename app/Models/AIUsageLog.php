<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIUsageLog extends Model
{
    use HasFactory;

    protected $fillable = [
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
}
