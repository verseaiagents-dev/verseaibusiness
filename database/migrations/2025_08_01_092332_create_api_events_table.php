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
        Schema::create('api_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('agent_id')->constrained()->onDelete('cascade');
            $table->foreignId('intent_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('http_method', ['GET', 'POST', 'PUT', 'DELETE', 'PATCH']);
            $table->string('endpoint_url');
            $table->json('headers')->nullable();
            $table->json('body_template')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('response_mapping')->nullable();
            $table->json('trigger_conditions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_events');
    }
};
