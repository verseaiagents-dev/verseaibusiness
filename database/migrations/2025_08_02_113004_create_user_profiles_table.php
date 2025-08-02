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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // 1. İşletme Bilgileri
            $table->string('username')->unique()->nullable(); // Public handle
            $table->string('business_name')->nullable(); // İşletme adı
            $table->string('profile_slug')->unique()->nullable(); // versai.com/profile/marka-adi
            $table->string('avatar_url')->nullable(); // Profil resmi ya da logo
            $table->text('bio')->nullable(); // Kısa açıklama
            $table->string('industry')->nullable(); // Hangi sektörde oldukları
            $table->string('location')->nullable(); // Şehir/ülke bilgisi
            
            // 2. İstatistikler (AI Destekli)
            $table->integer('total_sessions')->default(0); // Toplam sohbet sayısı
            $table->integer('total_events_tracked')->default(0); // Takip edilen toplam event sayısı
            $table->decimal('conversion_rate', 5, 2)->default(0); // Dönüşüm oranı (%)
            $table->json('popular_topics')->nullable(); // En çok konuşulan konular
            $table->integer('response_quality_score')->default(0); // AI performans skoru (0-100)
            
            // 3. Sosyal & İnteraktif
            $table->integer('reviews_count')->default(0); // Toplam müşteri yorumu sayısı
            $table->decimal('average_rating', 3, 2)->default(0); // Ortalama yıldız puanı (1-5)
            $table->json('featured_testimonials')->nullable(); // Öne çıkarılmış müşteri yorumları
            $table->string('share_qr_code_url')->nullable(); // QR kod bağlantısı
            
            // 4. Bağlantılar
            $table->string('website_url')->nullable(); // İşletmenin resmi web sitesi
            $table->json('social_links')->nullable(); // Sosyal medya linkleri
            $table->string('contact_email')->nullable(); // İletişim için email
            
            // 5. Gizlilik & Durum
            $table->boolean('is_public')->default(true); // Profilin herkese açık olup olmadığı
            $table->timestamp('last_active_at')->nullable(); // En son aktif olduğu zaman
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id']);
            $table->index(['username']);
            $table->index(['profile_slug']);
            $table->index(['is_public']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
