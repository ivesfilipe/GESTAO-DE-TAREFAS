<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_member_profiles', function (Blueprint $table) {
            $table->string('role')->nullable()->after('user_id');
            $table->string('department')->nullable()->after('role');
            $table->text('function_summary')->nullable()->after('department');
            $table->json('responsibilities')->nullable()->after('function_summary');
            $table->json('recurring_responsibilities')->nullable()->after('responsibilities');
            $table->json('professional_objectives')->nullable()->after('recurring_responsibilities');
            $table->text('delegation_guidelines')->nullable()->after('professional_objectives');
        });

        Schema::table('team_member_documents', function (Blueprint $table) {
            $table->string('processing_status')->default('pronto')->after('metadata');
            $table->text('processing_error')->nullable()->after('processing_status');
        });
    }

    public function down(): void
    {
        Schema::table('team_member_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'department',
                'function_summary',
                'responsibilities',
                'recurring_responsibilities',
                'professional_objectives',
                'delegation_guidelines',
            ]);
        });

        Schema::table('team_member_documents', function (Blueprint $table) {
            $table->dropColumn(['processing_status', 'processing_error']);
        });
    }
};
