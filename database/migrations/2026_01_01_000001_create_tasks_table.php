<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->restrictOnDelete();
            $table->enum('priority', ['normal', 'importante', 'urgente', 'critica'])->default('normal');
            $table->enum('status', ['nao_atribuida', 'nova', 'recebida', 'em_andamento', 'aguardando_aprovacao', 'concluida', 'bloqueada', 'reprovada', 'cancelada'])->default('nao_atribuida');
            $table->dateTime('due_at');
            $table->dateTime('original_due_at');
            $table->dateTime('completed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->text('block_reason')->nullable();
            $table->string('blocked_on')->nullable();
            $table->enum('rejection_category', ['nao_atende', 'escopo_mudou', 'info_incompleta', 'outro'])->nullable();
            $table->text('rejection_note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->index(['assigned_to', 'status']);
            $table->index('due_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
