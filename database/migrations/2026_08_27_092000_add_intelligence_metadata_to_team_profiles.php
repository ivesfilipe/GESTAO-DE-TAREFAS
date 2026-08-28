<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_member_profiles', function (Blueprint $table) {
            $table->json('ai_summary_sources')->nullable()->after('preferences');
            $table->timestamp('summary_invalidated_at')->nullable()->after('generated_at');
        });
    }

    public function down(): void
    {
        Schema::table('team_member_profiles', function (Blueprint $table) {
            $table->dropColumn(['ai_summary_sources', 'summary_invalidated_at']);
        });
    }
};
