<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const COLUMNS_BY_TABLE = [
        'tasks' => ['created_at', 'updated_at', 'deleted_at', 'due_at', 'original_due_at', 'completed_at', 'recurrence_next_at', 'scheduled_start'],
        'comments' => ['created_at', 'updated_at'],
        'attachments' => ['created_at', 'updated_at'],
        'task_history_events' => ['created_at', 'updated_at'],
        'change_requests' => ['created_at', 'updated_at', 'resolved_at'],
        'notifications' => ['created_at', 'updated_at', 'read_at'],
        'users' => ['created_at', 'updated_at', 'deleted_at', 'invited_at', 'activated_at', 'email_verified_at'],
    ];

    public function up(): void
    {
        $this->shift('SUB');
    }

    public function down(): void
    {
        $this->shift('ADD');
    }

    private function shift(string $direction): void
    {
        $driver = DB::getDriverName();
        $modifier = ($direction === 'SUB' ? '-' : '+').'3 hours';

        foreach (self::COLUMNS_BY_TABLE as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $updates = [];

            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                $updates[$column] = DB::raw(match ($driver) {
                    'sqlite' => "datetime($column, '$modifier')",
                    default => "IF($column IS NULL, NULL, DATE_{$direction}($column, INTERVAL 3 HOUR))",
                });
            }

            if ($updates !== []) {
                DB::table($table)->update($updates);
            }
        }
    }
};
