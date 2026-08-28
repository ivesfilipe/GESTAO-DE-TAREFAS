<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'manager_id',
        'timezone',
        'invited_at',
        'activated_at',
        'is_active',
        'calendar_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'invited_at' => 'datetime',
            'activated_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $user) {
            $user->calendar_token ??= (string) Str::uuid();

            if ($user->isLiderado() && $user->manager_id === null) {
                $gestorIds = self::query()
                    ->where('role', 'gestor')
                    ->where('is_active', true)
                    ->limit(2)
                    ->pluck('id');

                if ($gestorIds->count() === 1) {
                    $user->manager_id = $gestorIds->first();
                }
            }
        });
    }

    public function isGestor(): bool
    {
        return $this->role === 'gestor';
    }

    public function isLiderado(): bool
    {
        return $this->role === 'liderado';
    }

    public function createdTasks()
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function manager()
    {
        return $this->belongsTo(self::class, 'manager_id');
    }

    public function teamMembers()
    {
        return $this->hasMany(self::class, 'manager_id');
    }

    public function scopeManagedBy(Builder $query, self $manager): Builder
    {
        return $query->where('manager_id', $manager->id);
    }

    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function webhookEndpoints()
    {
        return $this->hasMany(WebhookEndpoint::class);
    }

    public function teamProfile()
    {
        return $this->hasOne(TeamMemberProfile::class);
    }

    public function documents()
    {
        return $this->hasMany(TeamMemberDocument::class);
    }

    public function chunks()
    {
        return $this->hasMany(TeamMemberKnowledgeChunk::class);
    }
}
