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
        Schema::table('agents', function (Blueprint $table) {
            // Yeni alanları ekle
            if (!Schema::hasColumn('agents', 'name')) {
                $table->string('name')->after('id');
            }
            if (!Schema::hasColumn('agents', 'sector')) {
                $table->enum('sector', ['ecommerce', 'real_estate', 'tourism'])->after('name');
            }
            if (!Schema::hasColumn('agents', 'description')) {
                $table->text('description')->nullable()->after('sector');
            }
            if (!Schema::hasColumn('agents', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('description');
            }
            if (!Schema::hasColumn('agents', 'config')) {
                $table->json('config')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('agents', 'api_credentials')) {
                $table->json('api_credentials')->nullable()->after('config');
            }
            if (!Schema::hasColumn('agents', 'intent_threshold')) {
                $table->integer('intent_threshold')->default(75)->after('api_credentials');
            }
            if (!Schema::hasColumn('agents', 'model_settings')) {
                $table->json('model_settings')->nullable()->after('intent_threshold');
            }
            if (!Schema::hasColumn('agents', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropColumn([
                'name', 'sector', 'description', 'is_active', 
                'config', 'api_credentials', 'intent_threshold', 
                'model_settings', 'deleted_at'
            ]);
        });
    }
};
