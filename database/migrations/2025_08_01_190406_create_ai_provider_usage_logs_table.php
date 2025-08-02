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
        Schema::create('ai_provider_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('ai_providers')->onDelete('cascade');
            $table->foreignId('model_id')->nullable()->constrained('ai_provider_models')->onDelete('set null');
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('set null');
            $table->integer('tokens_used')->default(0);
            $table->decimal('cost', 10, 6)->default(0);
            $table->integer('response_time')->nullable(); // Milisaniye cinsinden
            $table->enum('status', ['success', 'failed'])->default('success');
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['provider_id', 'created_at']);
            $table->index(['project_id', 'created_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_provider_usage_logs');
    }
}; 