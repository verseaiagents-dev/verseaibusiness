# OpenRouter AI Provider Entegrasyonu

Bu dokümantasyon, VersAI projesine OpenRouter AI provider entegrasyonunu açıklar.

## OpenRouter Nedir?

OpenRouter, farklı AI provider'larının modellerini tek bir API üzerinden sunan bir servistir. OpenAI, Anthropic, Google, Meta, Mistral gibi provider'ların modellerine erişim sağlar.

## Özellikler

- ✅ OpenAI modelleri (GPT-4o, GPT-4o-mini, GPT-4-turbo, GPT-3.5-turbo)
- ✅ Anthropic modelleri (Claude 3.5 Sonnet, Claude 3 Haiku, Claude 3 Opus)
- ✅ Meta modelleri (Llama 3.1 8B/70B/405B Instruct)
- ✅ Google modelleri (Gemini 2.0/2.5 Flash)
- ✅ Mistral modelleri (Mistral 7B, Mixtral 8x7B)
- ✅ Streaming desteği
- ✅ Token kullanım takibi
- ✅ Maliyet hesaplama

## Kurulum

### 1. Migration'ları çalıştırın

```bash
php artisan migrate
```

### 2. Seeder'ı çalıştırın

```bash
php artisan db:seed --class=AiProviderSeeder
```

### 3. API Key'i yapılandırın

Admin panelinden veya direkt veritabanından OpenRouter API key'inizi ekleyin.

## Kullanım

### Console Command ile Test

```bash
# API key ile test
php artisan test:openrouter --api-key=your_openrouter_api_key

# Farklı model ile test
php artisan test:openrouter --api-key=your_key --model=anthropic/claude-3.5-sonnet
```

### Programatik Kullanım

```php
use App\Services\AiChatService;
use App\Models\AiProvider;
use App\Models\AiProviderModel;

// OpenRouter provider'ını bul
$provider = AiProvider::where('provider_type', 'openrouter')->first();

// Model seç
$model = $provider->models()->where('model_name', 'openai/gpt-4o')->first();

// Chat service'i kullan
$chatService = app(AiChatService::class);
$result = $chatService->sendMessage(
    $provider->id,
    $model->id,
    'Merhaba! Nasılsın?',
    [
        'system' => 'Sen yardımcı bir AI asistanısın.',
        'max_tokens' => 1000,
        'temperature' => 0.7
    ]
);

if ($result['success']) {
    echo "Response: " . $result['content'];
    echo "Tokens: " . $result['tokens'];
    echo "Cost: $" . $result['cost'];
}
```

### Streaming Kullanımı

```php
$result = $chatService->streamMessage(
    $provider->id,
    $model->id,
    'Uzun bir hikaye anlat',
    ['max_tokens' => 2000]
);

if ($result['success']) {
    // Stream response'u işle
    $streamData = $result['stream'];
    // Frontend'de SSE ile işle
}
```

## Mevcut Modeller

### OpenAI Modelleri
- `openai/gpt-4o` - GPT-4o (En güncel)
- `openai/gpt-4o-mini` - GPT-4o Mini (Hızlı)
- `openai/gpt-4-turbo` - GPT-4 Turbo
- `openai/gpt-3.5-turbo` - GPT-3.5 Turbo

### Anthropic Modelleri
- `anthropic/claude-3.5-sonnet` - Claude 3.5 Sonnet
- `anthropic/claude-3-haiku` - Claude 3 Haiku
- `anthropic/claude-3-opus` - Claude 3 Opus

### Meta Modelleri
- `meta-llama/llama-3.1-8b-instruct` - Llama 3.1 8B
- `meta-llama/llama-3.1-70b-instruct` - Llama 3.1 70B
- `meta-llama/llama-3.1-405b-instruct` - Llama 3.1 405B

### Google Modelleri
- `google/gemini-2.0-flash-exp` - Gemini 2.0 Flash
- `google/gemini-2.5-flash-exp` - Gemini 2.5 Flash

### Mistral Modelleri
- `mistralai/mistral-7b-instruct` - Mistral 7B
- `mistralai/mixtral-8x7b-instruct` - Mixtral 8x7B

## API Endpoint

OpenRouter API endpoint'i: `https://openrouter.ai/api/v1`

### Chat Completions

```bash
curl https://openrouter.ai/api/v1/chat/completions \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $OPENROUTER_API_KEY" \
  -d '{
  "model": "openai/gpt-4o",
  "messages": [
    {
      "role": "user",
      "content": "What is the meaning of life?"
    }
  ]
}'
```

## Maliyet

OpenRouter, her model için farklı fiyatlandırma yapar. Maliyet bilgileri için [OpenRouter Pricing](https://openrouter.ai/pricing) sayfasını ziyaret edin.

## Hata Kodları

- `401` - API key geçersiz
- `403` - Erişim izni yok
- `404` - Model bulunamadı
- `429` - Rate limit aşıldı

## Güvenlik

- API key'ler şifrelenmiş olarak saklanır
- Rate limiting uygulanır
- Kullanım logları tutulur

## Sorun Giderme

### API Key Hatası
```bash
php artisan test:openrouter --api-key=your_key
```

### Model Bulunamadı
```bash
# Modelleri senkronize et
php artisan tinker
>>> $provider = App\Models\AiProvider::where('provider_type', 'openrouter')->first();
>>> app(App\Services\AiProviderService::class)->syncProviderModels($provider->id);
```

### Bağlantı Sorunu
- API key'in doğru olduğundan emin olun
- İnternet bağlantınızı kontrol edin
- OpenRouter servis durumunu kontrol edin

## Geliştirme

### Yeni Model Ekleme

1. `AiProviderService::syncOpenRouterModels()` metodunu güncelleyin
2. Yeni modeli `$models` array'ine ekleyin
3. Seeder'ı çalıştırın

### Yeni Provider Tipi Ekleme

1. Migration'da enum'a yeni değeri ekleyin
2. `AiProviderService`'e test ve sync metodları ekleyin
3. `AiChatService`'e request metodları ekleyin
4. Controller'da validation kurallarını güncelleyin

## Katkıda Bulunma

1. Fork yapın
2. Feature branch oluşturun
3. Değişikliklerinizi commit edin
4. Pull request gönderin

## Lisans

Bu entegrasyon MIT lisansı altında lisanslanmıştır. 