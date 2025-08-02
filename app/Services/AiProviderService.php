<?php

namespace App\Services;

use App\Models\AiProvider;
use App\Models\AiProviderModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiProviderService
{
    /**
     * Aktif provider'ları getir
     */
    public function getActiveProviders(): Collection
    {
        return AiProvider::active()->byPriority()->get();
    }

    /**
     * Öncelik sırasına göre provider getir
     */
    public function getProviderByPriority(): ?AiProvider
    {
        return AiProvider::active()->byPriority()->first();
    }

    /**
     * Provider bağlantısını test et
     */
    public function testProviderConnection(int $providerId): array
    {
        $provider = AiProvider::find($providerId);
        
        if (!$provider) {
            return [
                'success' => false,
                'message' => 'Provider bulunamadı'
            ];
        }

        if (!$provider->hasApiKey()) {
            return [
                'success' => false,
                'message' => 'API key yapılandırılmamış'
            ];
        }

        try {
            $startTime = microtime(true);
            
            switch ($provider->provider_type) {
                case 'openai':
                    $result = $this->testOpenAiConnection($provider);
                    break;
                case 'claude':
                    $result = $this->testClaudeConnection($provider);
                    break;
                case 'xai':
                    $result = $this->testXaiConnection($provider);
                    break;
                case 'deepseek':
                    $result = $this->testDeepSeekConnection($provider);
                    break;
                case 'gemini':
                    $result = $this->testGeminiConnection($provider);
                    break;
                case 'voyage':
                    $result = $this->testVoyageConnection($provider);
                    break;
                case 'custom':
                    $result = $this->testCustomConnection($provider);
                    break;
                default:
                    $result = [
                        'success' => false,
                        'message' => 'Desteklenmeyen provider tipi'
                    ];
            }

            $responseTime = (microtime(true) - $startTime) * 1000;
            $result['response_time'] = round($responseTime, 2);

            return $result;

        } catch (\Exception $e) {
            Log::error('AI Provider test connection failed', [
                'provider_id' => $providerId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Bağlantı testi başarısız: ' . $e->getMessage(),
                'response_time' => 0
            ];
        }
    }

    /**
     * Provider modellerini getir
     */
    public function getProviderModels(int $providerId): Collection
    {
        return AiProviderModel::byProvider($providerId)->available()->get();
    }

    /**
     * Provider ayarlarını güncelle
     */
    public function updateProviderSettings(int $providerId, array $settings): bool
    {
        $provider = AiProvider::find($providerId);
        
        if (!$provider) {
            return false;
        }

        $provider->settings = array_merge($provider->settings ?? [], $settings);
        return $provider->save();
    }

    /**
     * Custom provider ekle
     */
    public function addCustomProvider(array $data): AiProvider
    {
        $data['provider_type'] = 'custom';
        $data['is_active'] = true;
        $data['priority'] = $data['priority'] ?? 999; // En düşük öncelik

        return AiProvider::create($data);
    }

    /**
     * Provider'ı aktif/pasif yap
     */
    public function toggleProviderStatus(int $providerId): bool
    {
        $provider = AiProvider::find($providerId);
        
        if (!$provider) {
            return false;
        }

        $provider->is_active = !$provider->is_active;
        return $provider->save();
    }

    /**
     * Provider'ı sil
     */
    public function deleteProvider(int $providerId): bool
    {
        $provider = AiProvider::find($providerId);
        
        if (!$provider) {
            return false;
        }

        return $provider->delete();
    }

    /**
     * Provider'dan modelleri senkronize et
     */
    public function syncProviderModels(int $providerId): array
    {
        $provider = AiProvider::find($providerId);
        
        if (!$provider) {
            return [
                'success' => false,
                'message' => 'Provider bulunamadı'
            ];
        }

        try {
            switch ($provider->provider_type) {
                case 'openai':
                    return $this->syncOpenAiModels($provider);
                case 'claude':
                    return $this->syncClaudeModels($provider);
                case 'gemini':
                    return $this->syncGeminiModels($provider);
                default:
                    return [
                        'success' => false,
                        'message' => 'Bu provider için model senkronizasyonu desteklenmiyor'
                    ];
            }
        } catch (\Exception $e) {
            Log::error('Model sync failed', [
                'provider_id' => $providerId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Model senkronizasyonu başarısız: ' . $e->getMessage()
            ];
        }
    }

    // Private test methods for different providers
    private function testOpenAiConnection(AiProvider $provider): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $provider->getApiKey(),
            'Content-Type' => 'application/json'
        ])->post($provider->getBaseUrl() ?: 'https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => 'Hello']
            ],
            'max_tokens' => 10
        ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'message' => 'OpenAI bağlantısı başarılı'
            ];
        }

        $errorData = $response->json();
        $errorMessage = 'OpenAI bağlantısı başarısız';
        
        if (isset($errorData['error']['code'])) {
            switch ($errorData['error']['code']) {
                case 429:
                    $errorMessage = 'OpenAI API rate limiti aşıldı. Lütfen birkaç dakika bekleyin.';
                    break;
                case 401:
                    $errorMessage = 'OpenAI API anahtarı geçersiz.';
                    break;
                case 403:
                    $errorMessage = 'OpenAI API erişim izni yok.';
                    break;
                case 404:
                    $errorMessage = 'OpenAI modeli bulunamadı.';
                    break;
                default:
                    $errorMessage = 'OpenAI bağlantısı başarısız: ' . ($errorData['error']['message'] ?? 'Bilinmeyen hata');
            }
        }
        
        return [
            'success' => false,
            'message' => $errorMessage
        ];
    }

    private function testClaudeConnection(AiProvider $provider): array
    {
        $response = Http::withHeaders([
            'x-api-key' => $provider->getApiKey(),
            'Content-Type' => 'application/json',
            'anthropic-version' => '2023-06-01'
        ])->post($provider->getBaseUrl() ?: 'https://api.anthropic.com/v1/messages', [
            'model' => 'claude-3-haiku-20240307',
            'max_tokens' => 10,
            'messages' => [
                ['role' => 'user', 'content' => 'Hello']
            ]
        ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'message' => 'Claude bağlantısı başarılı'
            ];
        }

        $errorData = $response->json();
        $errorMessage = 'Claude bağlantısı başarısız';
        
        if (isset($errorData['error']['code'])) {
            switch ($errorData['error']['code']) {
                case 429:
                    $errorMessage = 'Claude API rate limiti aşıldı. Lütfen birkaç dakika bekleyin.';
                    break;
                case 401:
                    $errorMessage = 'Claude API anahtarı geçersiz.';
                    break;
                case 403:
                    $errorMessage = 'Claude API erişim izni yok.';
                    break;
                case 404:
                    $errorMessage = 'Claude modeli bulunamadı.';
                    break;
                default:
                    $errorMessage = 'Claude bağlantısı başarısız: ' . ($errorData['error']['message'] ?? 'Bilinmeyen hata');
            }
        }
        
        return [
            'success' => false,
            'message' => $errorMessage
        ];
    }

    private function testXaiConnection(AiProvider $provider): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $provider->getApiKey(),
            'Content-Type' => 'application/json'
        ])->post($provider->getBaseUrl() ?: 'https://api.x.ai/v1/chat/completions', [
            'model' => 'grok-beta',
            'messages' => [
                ['role' => 'user', 'content' => 'Hello']
            ],
            'max_tokens' => 10
        ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'message' => 'xAI bağlantısı başarılı'
            ];
        }

        return [
            'success' => false,
            'message' => 'xAI bağlantısı başarısız: ' . $response->body()
        ];
    }

    private function testDeepSeekConnection(AiProvider $provider): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $provider->getApiKey(),
            'Content-Type' => 'application/json'
        ])->post($provider->getBaseUrl() ?: 'https://api.deepseek.com/v1/chat/completions', [
            'model' => 'deepseek-chat',
            'messages' => [
                ['role' => 'user', 'content' => 'Hello']
            ],
            'max_tokens' => 10
        ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'message' => 'DeepSeek bağlantısı başarılı'
            ];
        }

        return [
            'success' => false,
            'message' => 'DeepSeek bağlantısı başarısız: ' . $response->body()
        ];
    }

    private function testGeminiConnection(AiProvider $provider): array
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent?key=' . $provider->getApiKey(), [
            'contents' => [
                ['parts' => [['text' => 'Hello']]]
            ],
            'generationConfig' => [
                'maxOutputTokens' => 10
            ]
        ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'message' => 'Gemini bağlantısı başarılı'
            ];
        }

        $errorData = $response->json();
        $errorMessage = 'Gemini bağlantısı başarısız';
        
        if (isset($errorData['error']['code'])) {
            switch ($errorData['error']['code']) {
                case 429:
                    $errorMessage = 'Gemini API quota limiti aşıldı. Lütfen birkaç dakika bekleyin.';
                    break;
                case 401:
                    $errorMessage = 'Gemini API anahtarı geçersiz.';
                    break;
                case 403:
                    $errorMessage = 'Gemini API erişim izni yok.';
                    break;
                case 404:
                    $errorMessage = 'Gemini modeli bulunamadı.';
                    break;
                default:
                    $errorMessage = 'Gemini bağlantısı başarısız: ' . ($errorData['error']['message'] ?? 'Bilinmeyen hata');
            }
        }
        
        return [
            'success' => false,
            'message' => $errorMessage
        ];
    }

    private function testVoyageConnection(AiProvider $provider): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $provider->getApiKey(),
            'Content-Type' => 'application/json'
        ])->post($provider->getBaseUrl() ?: 'https://api.voyageai.com/v1/embeddings', [
            'model' => 'voyage-01',
            'input' => 'Hello'
        ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'message' => 'Voyage bağlantısı başarılı'
            ];
        }

        return [
            'success' => false,
            'message' => 'Voyage bağlantısı başarısız: ' . $response->body()
        ];
    }

    private function testOpenRouterConnection(AiProvider $provider): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $provider->getApiKey(),
            'Content-Type' => 'application/json'
        ])->post($provider->getBaseUrl() . '/chat/completions', [
            'model' => 'openai/gpt-4o',
            'messages' => [
                ['role' => 'user', 'content' => 'Hello']
            ],
            'max_tokens' => 10
        ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'message' => 'OpenRouter bağlantısı başarılı'
            ];
        }

        $errorData = $response->json();
        $errorMessage = 'OpenRouter bağlantısı başarısız';
        
        if (isset($errorData['error']['code'])) {
            switch ($errorData['error']['code']) {
                case 429:
                    $errorMessage = 'OpenRouter API rate limiti aşıldı. Lütfen birkaç dakika bekleyin.';
                    break;
                case 401:
                    $errorMessage = 'OpenRouter API anahtarı geçersiz.';
                    break;
                case 403:
                    $errorMessage = 'OpenRouter API erişim izni yok.';
                    break;
                case 404:
                    $errorMessage = 'OpenRouter modeli bulunamadı.';
                    break;
                default:
                    $errorMessage = 'OpenRouter bağlantısı başarısız: ' . ($errorData['error']['message'] ?? 'Bilinmeyen hata');
            }
        }
        
        return [
            'success' => false,
            'message' => $errorMessage
        ];
    }

    private function testCustomConnection(AiProvider $provider): array
    {
        if (!$provider->getBaseUrl()) {
            return [
                'success' => false,
                'message' => 'Custom provider için base URL gerekli'
            ];
        }

        $response = Http::timeout(10)->get($provider->getBaseUrl());

        if ($response->successful()) {
            return [
                'success' => true,
                'message' => 'Custom provider bağlantısı başarılı'
            ];
        }

        return [
            'success' => false,
            'message' => 'Custom provider bağlantısı başarısız: ' . $response->body()
        ];
    }

    // Private sync methods
    private function syncOpenAiModels(AiProvider $provider): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $provider->getApiKey()
        ])->get('https://api.openai.com/v1/models');

        if (!$response->successful()) {
            return [
                'success' => false,
                'message' => 'OpenAI modelleri alınamadı'
            ];
        }

        $models = $response->json('data', []);
        $syncedCount = 0;

        foreach ($models as $model) {
            if (str_contains($model['id'], 'gpt') || str_contains($model['id'], 'dall-e')) {
                AiProviderModel::updateOrCreate(
                    ['model_name' => $model['id'], 'provider_id' => $provider->id],
                    [
                        'display_name' => $model['id'],
                        'is_available' => true,
                        'features' => ['chat_completion']
                    ]
                );
                $syncedCount++;
            }
        }

        return [
            'success' => true,
            'message' => "{$syncedCount} OpenAI modeli senkronize edildi"
        ];
    }

    private function syncClaudeModels(AiProvider $provider): array
    {
        // Claude için sabit modeller (API'den model listesi alınamıyor)
        $models = [
            'claude-3-opus-20240229' => 'Claude 3 Opus',
            'claude-3-sonnet-20240229' => 'Claude 3 Sonnet',
            'claude-3-haiku-20240307' => 'Claude 3 Haiku'
        ];

        $syncedCount = 0;

        foreach ($models as $modelName => $displayName) {
            AiProviderModel::updateOrCreate(
                ['model_name' => $modelName, 'provider_id' => $provider->id],
                [
                    'display_name' => $displayName,
                    'is_available' => true,
                    'features' => ['chat_completion']
                ]
            );
            $syncedCount++;
        }

        return [
            'success' => true,
            'message' => "{$syncedCount} Claude modeli senkronize edildi"
        ];
    }

    private function syncGeminiModels(AiProvider $provider): array
    {
        // Gemini için güncel modeller
        $models = [
            'gemini-1.5-pro' => 'Gemini 1.5 Pro',
            'gemini-2.0-flash' => 'Gemini 2.0 Flash',
            'gemini-2.5-pro' => 'Gemini 2.5 Pro',
            'gemini-2.5-flash' => 'Gemini 2.5 Flash'
        ];

        $syncedCount = 0;

        foreach ($models as $modelName => $displayName) {
            AiProviderModel::updateOrCreate(
                ['model_name' => $modelName, 'provider_id' => $provider->id],
                [
                    'display_name' => $displayName,
                    'is_available' => true,
                    'features' => ['chat_completion']
                ]
            );
            $syncedCount++;
        }

        return [
            'success' => true,
            'message' => "{$syncedCount} Gemini modeli senkronize edildi"
        ];
    }

    private function syncOpenRouterModels(AiProvider $provider): array
    {
        // OpenRouter için popüler modeller
        $models = [
            'openai/gpt-4o' => 'GPT-4o (OpenAI)',
            'openai/gpt-4o-mini' => 'GPT-4o Mini (OpenAI)',
            'openai/gpt-4-turbo' => 'GPT-4 Turbo (OpenAI)',
            'openai/gpt-3.5-turbo' => 'GPT-3.5 Turbo (OpenAI)',
            'anthropic/claude-3.5-sonnet' => 'Claude 3.5 Sonnet (Anthropic)',
            'anthropic/claude-3-haiku' => 'Claude 3 Haiku (Anthropic)',
            'meta-llama/llama-3.1-8b-instruct' => 'Llama 3.1 8B Instruct (Meta)',
            'meta-llama/llama-3.1-70b-instruct' => 'Llama 3.1 70B Instruct (Meta)',
            'google/gemini-2.0-flash-exp' => 'Gemini 2.0 Flash (Google)',
            'google/gemini-2.5-flash-exp' => 'Gemini 2.5 Flash (Google)',
            'mistralai/mistral-7b-instruct' => 'Mistral 7B Instruct',
            'mistralai/mixtral-8x7b-instruct' => 'Mixtral 8x7B Instruct',
            'meta-llama/llama-3.1-405b-instruct' => 'Llama 3.1 405B Instruct (Meta)',
            'anthropic/claude-3-opus' => 'Claude 3 Opus (Anthropic)',
            'openai/gpt-4' => 'GPT-4 (OpenAI)',
            'openai/gpt-4-32k' => 'GPT-4 32K (OpenAI)'
        ];

        $syncedCount = 0;

        foreach ($models as $modelName => $displayName) {
            AiProviderModel::updateOrCreate(
                ['model_name' => $modelName, 'provider_id' => $provider->id],
                [
                    'display_name' => $displayName,
                    'is_available' => true,
                    'features' => ['chat_completion'],
                    'max_tokens' => 4000,
                    'cost_per_1k_tokens' => 0.01 // Varsayılan değer
                ]
            );
            $syncedCount++;
        }

        return [
            'success' => true,
            'message' => "{$syncedCount} OpenRouter modeli senkronize edildi"
        ];
    }
} 