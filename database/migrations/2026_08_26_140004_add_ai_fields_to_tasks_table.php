<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('task_type', 30)->default('demanda')->after('status');
            $table->text('acceptance_criteria')->nullable()->after('task_type');
            $table->text('expected_evidence')->nullable()->after('acceptance_criteria');

            $table->index('task_type');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['task_type']);
            $table->dropColumn(['task_type', 'acceptance_criteria', 'expected_evidence']);
        });
    }
};
