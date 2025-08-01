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
        Schema::create('agent_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->onDelete('cascade');
            $table->enum('integration_type', ['woocommerce', 'shopify', 'magento', 'opencart', 'custom']);
            $table->string('name');
            $table->json('config')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sync')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_integrations');
    }
};
