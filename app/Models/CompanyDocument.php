<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyDocument extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uploaded_by',
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

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function chunks(): HasMany
    {
        return $this->hasMany(CompanyKnowledgeChunk::class, 'document_id');
    }
}
