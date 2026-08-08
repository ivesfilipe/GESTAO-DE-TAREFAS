<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Corrige drift de schema: a migration create_users_table foi editada no
 * commit 57f576c depois de já ter rodado em produção (bancos de lá ficaram
 * sem estas 6 colunas). Cada coluna é adicionada apenas se ausente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('liderado');
            }
            if (! Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone')->default('America/Sao_Paulo');
            }
            if (! Schema::hasColumn('users', 'invited_at')) {
                $table->timestamp('invited_at')->nullable();
            }
            if (! Schema::hasColumn('users', 'activated_at')) {
                $table->timestamp('activated_at')->nullable();
            }
            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            if (! Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'timezone', 'invited_at', 'activated_at', 'is_active', 'deleted_at']);
        });
    }
};
