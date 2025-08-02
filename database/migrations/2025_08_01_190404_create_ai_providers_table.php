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
        Schema::create('ai_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Provider adı: OpenAI, Claude, xAI, DeepSeek, Gemini, Voyage, Custom
            $table->string('display_name'); // Görünen ad
            $table->text('api_key')->nullable(); // Encrypted API key
            $table->string('base_url')->nullable(); // API endpoint - custom provider'lar için
            $table->string('default_model')->nullable(); // Varsayılan model
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // Öncelik sırası
            $table->enum('provider_type', ['openai', 'claude', 'xai', 'deepseek', 'gemini', 'voyage', 'openrouter', 'custom'])->default('openai');
            $table->json('settings')->nullable(); // Provider özel ayarları
            $table->timestamps();
            
            // Indexes
            $table->index(['is_active', 'priority']);
            $table->index('provider_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_providers');
    }
};
