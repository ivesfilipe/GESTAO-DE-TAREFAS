<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->restrictOnDelete();
            $table->foreignId('comment_id')->nullable()->constrained('comments')->restrictOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type', 50);
            $table->integer('file_size');
            $table->timestamp('created_at')->useCurrent();
            $table->softDeletes();
        });

        Schema::table('attachments', function (Blueprint $table) {
            $table->index('task_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
