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
        Schema::table('user_profiles', function (Blueprint $table) {
            // API Ayarları
            $table->string('reviews_api_type')->nullable()->after('contact_email'); // 'google_maps', 'custom_api', 'manual'
            $table->string('google_maps_place_id')->nullable()->after('reviews_api_type'); // Google Maps Place ID
            $table->string('google_maps_api_key')->nullable()->after('google_maps_place_id'); // Kullanıcının kendi API key'i
            $table->string('custom_api_url')->nullable()->after('google_maps_api_key'); // Özel API endpoint
            $table->string('custom_api_key')->nullable()->after('custom_api_url'); // Özel API key
            $table->json('custom_api_headers')->nullable()->after('custom_api_key'); // Özel API headers
            $table->boolean('auto_sync_reviews')->default(false)->after('custom_api_headers'); // Otomatik senkronizasyon
            $table->timestamp('last_reviews_sync')->nullable()->after('auto_sync_reviews'); // Son senkronizasyon zamanı
            $table->integer('sync_interval_hours')->default(24)->after('last_reviews_sync'); // Senkronizasyon aralığı (saat)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'reviews_api_type',
                'google_maps_place_id',
                'google_maps_api_key',
                'custom_api_url',
                'custom_api_key',
                'custom_api_headers',
                'auto_sync_reviews',
                'last_reviews_sync',
                'sync_interval_hours'
            ]);
        });
    }
}; 