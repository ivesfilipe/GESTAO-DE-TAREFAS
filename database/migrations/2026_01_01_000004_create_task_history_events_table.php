<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_history_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->restrictOnDelete();
            $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $table->string('event_type', 50);
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::table('task_history_events', function (Blueprint $table) {
            $table->index('task_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_history_events');
    }
};
