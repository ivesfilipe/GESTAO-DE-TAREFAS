<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_member_knowledge_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_id')->nullable()->constrained('team_member_documents')->nullOnDelete();
            $table->longText('content');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index('user_id');
            $table->index('document_id');
            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_member_knowledge_chunks');
    }
};
