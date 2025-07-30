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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Proje ismi
            $table->text('description')->nullable(); // Açıklama
            $table->integer('token_limit'); // Token limit
            $table->string('llm_model')->nullable()->default('gpt-3.5-turbo'); // LLM model (GPT 3.5, Claude Sonnet 3.5)
            $table->text('llm_behavior')->nullable(); // LLM davranış biçimi
            $table->string('sector_agent_model')->nullable(); // Sektörel Agent Model
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
