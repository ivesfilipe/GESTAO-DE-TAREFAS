<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('manager_id')->nullable()->after('role')->constrained('users')->nullOnDelete();
            $table->index('manager_id');
        });

        $gestors = DB::table('users')
            ->where('role', 'gestor')
            ->whereNull('deleted_at')
            ->pluck('id');

        if ($gestors->count() === 1) {
            DB::table('users')
                ->where('role', 'liderado')
                ->whereNull('manager_id')
                ->update(['manager_id' => $gestors->first()]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('manager_id');
        });
    }
};
