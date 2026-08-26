<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamMemberKnowledgeChunk extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function document()
    {
        return $this->belongsTo(TeamMemberDocument::class, 'document_id');
    }
}
