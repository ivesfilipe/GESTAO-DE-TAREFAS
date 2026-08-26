<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeamMemberDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'path',
        'mime_type',
        'size',
        'extracted_text',
        'metadata',
        'processing_status',
        'processing_error',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'size' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chunks()
    {
        return $this->hasMany(TeamMemberKnowledgeChunk::class, 'document_id');
    }

    public function markProcessing(string $status = 'processando', ?string $error = null): void
    {
        $this->update([
            'processing_status' => $status,
            'processing_error' => $error,
        ]);
    }
}
