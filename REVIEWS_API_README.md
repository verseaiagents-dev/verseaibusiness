# Müşteri Yorumları API Entegrasyonu

Bu özellik, kullanıcıların müşteri yorumlarını Google Maps API veya özel API'lerden otomatik olarak çekmesini sağlar.

## Özellikler

### 🔗 Desteklenen API'ler
- **Google Maps API**: İşletmenizin Google Maps'teki yorumlarını çeker
- **Özel API**: Kendi API'nizden yorumları çeker
- **Manuel**: Yorumları manuel olarak girer

### ⚙️ Yapılandırma Seçenekleri
- **Otomatik Senkronizasyon**: Yorumları belirli aralıklarla otomatik güncelle
- **Senkronizasyon Aralığı**: 1-168 saat arası ayarlanabilir
- **API Key Yönetimi**: Kendi API key'lerinizi kullanabilirsiniz

## Kurulum

### 1. Migration'ları Çalıştırın
```bash
php artisan migrate
```

### 2. Google Maps API Key'i Ayarlayın (Opsiyonel)
`.env` dosyasına ekleyin:
```
GOOGLE_MAPS_API_KEY=your_google_maps_api_key_here
```

### 3. Cron Job Ekleyin (Otomatik Senkronizasyon için)
`app/Console/Kernel.php` dosyasına ekleyin:
```php
protected function schedule(Schedule $schedule)
{
    // Her saat başı otomatik senkronizasyon
    $schedule->command('reviews:sync --all')->hourly();
}
```

## Kullanım

### Kullanıcı Paneli
1. Dashboard'da "API Ayarları" menüsüne gidin
2. API türünü seçin (Google Maps, Özel API, Manuel)
3. Gerekli bilgileri girin
4. "Bağlantıyı Test Et" ile API bağlantısını kontrol edin
5. "Yorumları Senkronize Et" ile yorumları çekin

### Google Maps Entegrasyonu
1. **Place ID Bulma**: 
   - Google Maps'te işletmenizi arayın
   - URL'den Place ID'yi kopyalayın
   - Veya API ayarları sayfasında "Ara" butonunu kullanın

2. **API Key (Opsiyonel)**:
   - Kendi Google Maps API key'inizi kullanabilirsiniz
   - Boş bırakırsanız sistem API key'i kullanır

### Özel API Entegrasyonu
1. **API Endpoint**: Yorumları döndüren endpoint URL'si
2. **API Key**: Gerekirse authentication için
3. **Headers**: Özel header'lar JSON formatında

#### Örnek API Response Formatı
```json
{
  "reviews": [
    {
      "name": "Müşteri Adı",
      "rating": 5,
      "comment": "Harika hizmet!",
      "date": 1640995200
    }
  ]
}
```

## Komut Satırı Kullanımı

### Belirli Bir Profili Senkronize Et
```bash
php artisan reviews:sync --profile-id=1
```

### Tüm Profilleri Senkronize Et
```bash
php artisan reviews:sync --all
```

## API Endpoints

### Kullanıcı API Ayarları
- `GET /reviews-api` - API ayarları sayfası
- `PUT /reviews-api/update` - API ayarlarını güncelle
- `POST /reviews-api/test-connection` - API bağlantısını test et
- `POST /reviews-api/sync` - Yorumları senkronize et
- `POST /reviews-api/search-places` - Google Maps işletme ara

## Veritabanı Yapısı

### Yeni Alanlar (user_profiles tablosu)
- `reviews_api_type`: API türü (google_maps, custom_api, manual)
- `google_maps_place_id`: Google Maps Place ID
- `google_maps_api_key`: Kullanıcının API key'i
- `custom_api_url`: Özel API endpoint
- `custom_api_key`: Özel API key
- `custom_api_headers`: Özel API header'ları (JSON)
- `auto_sync_reviews`: Otomatik senkronizasyon (boolean)
- `last_reviews_sync`: Son senkronizasyon zamanı
- `sync_interval_hours`: Senkronizasyon aralığı (saat)

## Güvenlik

- API key'ler şifrelenmiş olarak saklanır
- HTTPS zorunludur
- Rate limiting uygulanır
- Kullanıcı sadece kendi profilini düzenleyebilir

## Hata Ayıklama

### Yaygın Sorunlar

1. **Google Maps API Hatası**
   - Place ID'nin doğru olduğundan emin olun
   - API key'in geçerli olduğunu kontrol edin
   - API quota'sını kontrol edin

2. **Özel API Hatası**
   - Endpoint URL'sinin doğru olduğunu kontrol edin
   - API response formatını kontrol edin
   - Authentication bilgilerini kontrol edin

3. **Senkronizasyon Hatası**
   - Log dosyalarını kontrol edin: `storage/logs/laravel.log`
   - API ayarlarını tekrar kontrol edin

### Log Dosyaları
```bash
tail -f storage/logs/laravel.log
```

## Geliştirme

### Yeni API Entegrasyonu Ekleme
1. `ReviewsApiService` sınıfına yeni method ekleyin
2. API response formatını parse edin
3. Controller'da yeni API türünü ekleyin
4. View'da yeni seçenekleri ekleyin

### Test
```bash
# API bağlantısını test et
php artisan reviews:sync --profile-id=1

# Tüm profilleri test et
php artisan reviews:sync --all
```

## Destek

Sorun yaşarsanız:
1. Log dosyalarını kontrol edin
2. API ayarlarını doğrulayın
3. Network bağlantısını kontrol edin
4. API quota'sını kontrol edin 