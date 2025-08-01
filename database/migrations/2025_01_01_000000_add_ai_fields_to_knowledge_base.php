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
        Schema::table('knowledge_base', function (Blueprint $table) {
            // AI işlenmiş içerik
            $table->text('ai_processed_content')->nullable()->after('content');
            
            // AI özeti
            $table->text('ai_summary')->nullable()->after('ai_processed_content');
            
            // AI kategorileri (JSON)
            $table->json('ai_categories')->nullable()->after('ai_summary');
            
            // AI embeddings (vektör verileri)
            $table->json('ai_embeddings')->nullable()->after('ai_categories');
            
            // AI işleme durumu
            $table->enum('ai_processing_status', ['pending', 'processing', 'completed', 'failed'])
                  ->default('pending')
                  ->after('ai_embeddings');
            
            // AI meta verileri
            $table->json('ai_metadata')->nullable()->after('ai_processing_status');
            
            // AI işleme tarihi
            $table->timestamp('ai_processed_at')->nullable()->after('ai_metadata');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('knowledge_base', function (Blueprint $table) {
            $table->dropColumn([
                'ai_processed_content',
                'ai_summary', 
                'ai_categories',
                'ai_embeddings',
                'ai_processing_status',
                'ai_metadata',
                'ai_processed_at'
            ]);
        });
    }
}; 