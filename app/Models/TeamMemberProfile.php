<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeamMemberProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'role',
        'department',
        'function_summary',
        'responsibilities',
        'recurring_responsibilities',
        'professional_objectives',
        'delegation_guidelines',
        'summary',
        'strengths',
        'gaps',
        'preferences',
        'notes',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'strengths' => 'array',
            'gaps' => 'array',
            'preferences' => 'array',
            'responsibilities' => 'array',
            'recurring_responsibilities' => 'array',
            'professional_objectives' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
