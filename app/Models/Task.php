<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Task extends Model
{
    use HasFactory, Searchable, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'created_by',
        'assigned_to',
        'priority',
        'status',
        'due_at',
        'original_due_at',
        'completed_at',
        'approved_by',
        'block_reason',
        'blocked_on',
        'rejection_category',
        'rejection_note',
        'recurrence_frequency',
        'recurrence_next_at',
        'recurrence_series_id',
        'estimated_minutes',
        'scheduled_start',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'original_due_at' => 'datetime',
            'completed_at' => 'datetime',
            'recurrence_next_at' => 'datetime',
            'scheduled_start' => 'datetime',
        ];
    }

    public static function statuses(): array
    {
        return ['nao_atribuida', 'nova', 'recebida', 'em_andamento', 'aguardando_aprovacao', 'concluida', 'bloqueada', 'reprovada', 'cancelada'];
    }

    public static function priorities(): array
    {
        return ['normal', 'importante', 'urgente', 'critica'];
    }

    public static function rejectionCategories(): array
    {
        return ['nao_atende', 'escopo_mudou', 'info_incompleta', 'outro'];
    }

    public static function recurrenceFrequencies(): array
    {
        return ['diaria', 'semanal', 'quinzenal', 'mensal'];
    }

    public static function recurrenceInterval($frequency): ?\DateInterval
    {
        return match ($frequency) {
            'diaria' => new \DateInterval('P1D'),
            'semanal' => new \DateInterval('P7D'),
            'quinzenal' => new \DateInterval('P14D'),
            'mensal' => new \DateInterval('P1M'),
            default => null,
        };
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->orderBy('created_at');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    public function historyEvents()
    {
        return $this->hasMany(TaskHistoryEvent::class)->orderBy('created_at');
    }

    public function changeRequests()
    {
        return $this->hasMany(ChangeRequest::class);
    }

    public function recurrenceSiblings()
    {
        return $this->hasMany(Task::class, 'recurrence_series_id', 'recurrence_series_id')
            ->whereNotNull('recurrence_series_id');
    }

    public function isRecurring(): bool
    {
        return $this->recurrence_frequency !== null;
    }

    public function searchableAs(): string
    {
        return 'tasks_index';
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->getKey(),
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'created_by' => $this->created_by,
            'assigned_to' => $this->assigned_to,
        ];
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['bloqueada', 'concluida', 'cancelada'])
            ->whereNotNull('due_at')
            ->where('due_at', '<', now());
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isGestor()) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user) {
            $q->where('assigned_to', $user->id)->orWhere('created_by', $user->id);
        });
    }

    public function isOverdue(): bool
    {
        if ($this->status === 'bloqueada' || $this->status === 'concluida' || $this->status === 'cancelada') {
            return false;
        }

        $timezone = $this->assignee?->timezone ?? 'America/Sao_Paulo';
        $now = now($timezone);

        return $this->due_at && $this->due_at->lt($now);
    }
}
