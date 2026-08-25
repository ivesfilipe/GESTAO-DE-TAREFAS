<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('recurrence_frequency', 20)->nullable()->after('blocked_on');
            $table->dateTime('recurrence_next_at')->nullable()->after('recurrence_frequency');
            $table->string('recurrence_series_id', 26)->nullable()->after('recurrence_next_at');

            $table->index(['recurrence_next_at']);
            $table->index(['recurrence_series_id']);
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['recurrence_next_at']);
            $table->dropIndex(['recurrence_series_id']);
            $table->dropColumn(['recurrence_frequency', 'recurrence_next_at', 'recurrence_series_id']);
        });
    }
};
