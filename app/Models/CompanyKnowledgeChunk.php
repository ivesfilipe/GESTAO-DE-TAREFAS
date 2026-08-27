<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyKnowledgeChunk extends Model
{
    protected $fillable = [
        'document_id',
        'content',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(CompanyDocument::class, 'document_id');
    }
}
