<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->longText('extracted_text')->nullable();
            $table->json('metadata')->nullable();
            $table->string('processing_status')->default('processando');
            $table->text('processing_error')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('uploaded_by');
            $table->index('processing_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_documents');
    }
};
