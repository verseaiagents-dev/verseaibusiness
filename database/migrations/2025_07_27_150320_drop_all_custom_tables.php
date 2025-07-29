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
        // Drop all custom tables in the correct order (respecting foreign key constraints)
        
        // Drop tables with foreign keys first (child tables)
        Schema::dropIfExists('agent_training_versions');
        
        // Drop tables that are referenced by other tables
        Schema::dropIfExists('agents');
        
        // Drop tables that are referenced by agents
        Schema::dropIfExists('agent_models');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is destructive, so down() will not recreate the tables
        // You would need to run the original migrations again if needed
    }
};
