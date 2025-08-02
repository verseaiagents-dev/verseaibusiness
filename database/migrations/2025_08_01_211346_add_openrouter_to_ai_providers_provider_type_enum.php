<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL için enum değerini ekle
        DB::statement("ALTER TABLE ai_providers MODIFY COLUMN provider_type ENUM('openai', 'claude', 'xai', 'deepseek', 'gemini', 'voyage', 'openrouter', 'custom') DEFAULT 'openai'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // MySQL için enum değerini kaldır
        DB::statement("ALTER TABLE ai_providers MODIFY COLUMN provider_type ENUM('openai', 'claude', 'xai', 'deepseek', 'gemini', 'voyage', 'custom') DEFAULT 'openai'");
    }
};
