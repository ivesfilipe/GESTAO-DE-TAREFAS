<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_knowledge_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->nullable()->constrained('company_documents')->nullOnDelete();
            $table->longText('content');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index('document_id');
            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_knowledge_chunks');
    }
};
