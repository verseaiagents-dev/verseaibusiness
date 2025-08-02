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
        Schema::create('ai_provider_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('ai_providers')->onDelete('cascade');
            $table->string('model_name'); // Model adı
            $table->string('display_name'); // Görünen ad
            $table->boolean('is_available')->default(true);
            $table->integer('max_tokens')->nullable(); // Maksimum token sayısı
            $table->decimal('cost_per_1k_tokens', 10, 6)->nullable(); // 1K token başına maliyet
            $table->json('features')->nullable(); // Model özellikleri: streaming, vision, etc.
            $table->timestamps();
            
            // Indexes
            $table->index(['provider_id', 'is_available']);
            $table->index('model_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_provider_models');
    }
}; 