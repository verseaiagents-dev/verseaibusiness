<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('knowledge_base', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type'); // csv, xml, txt, docx
            $table->integer('file_size');
            $table->text('content')->nullable(); // Extracted content
            $table->json('metadata')->nullable(); // Additional file metadata
            $table->enum('status', ['processing', 'completed', 'failed', 'active'])->default('processing');
            
            // AI Processing Fields
            $table->text('ai_processed_content')->nullable();
            $table->text('ai_summary')->nullable();
            $table->json('ai_categories')->nullable();
            $table->json('ai_embeddings')->nullable();
            $table->enum('ai_processing_status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->json('ai_metadata')->nullable();
            $table->timestamp('ai_processed_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_base');
    }
};
