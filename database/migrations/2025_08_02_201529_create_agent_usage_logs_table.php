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
        Schema::create('agent_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider'); // 'versai', 'openai', 'anthropic', etc.
            $table->string('model'); // 'gpt-4', 'gpt-3.5-turbo', etc.
            $table->integer('input_tokens')->default(0);
            $table->integer('output_tokens')->default(0);
            $table->decimal('input_cost', 10, 6)->default(0); // Cost per 1K input tokens
            $table->decimal('output_cost', 10, 6)->default(0); // Cost per 1K output tokens
            $table->decimal('total_cost', 10, 6)->default(0); // Total cost for this request
            $table->string('currency', 3)->default('USD');
            $table->json('metadata')->nullable(); // Additional data like request_id, response_time, etc.
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['agent_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['provider', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_usage_logs');
    }
};
