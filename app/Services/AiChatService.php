<?php

namespace App\Services;

use App\Models\AiProvider;
use App\Models\AiProviderModel;
use App\Models\AiProviderUsageLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatService
{
    private $providerService;

    public function __construct(AiProviderService $providerService)
    {
        $this->providerService = $providerService;
    }

    /**
     * Mesaj gönder
     */
    public function sendMessage(int $providerId, int $modelId, string $message, array $context = []): array
    {
        $provider = AiProvider::find($providerId);
        $model = AiProviderModel::find($modelId);

        if (!$provider || !$model) {
            return [
                'success' => false,
                'message' => 'Provider veya model bulunamadı'
            ];
        }

        if (!$provider->isActive()) {
            return [
                'success' => false,
                'message' => 'Provider aktif değil'
            ];
        }

        if (!$model->isAvailable()) {
            return [
                'success' => false,
                'message' => 'Model kullanılabilir değil'
            ];
        }

        $startTime = microtime(true);

        try {
            $response = $this->sendRequestToProvider($provider, $model, $message, $context);
            
            $responseTime = (microtime(true) - $startTime) * 1000;
            
            // Kullanım logunu kaydet
            $this->logUsage($providerId, $modelId, $response['tokens'] ?? 0, $response['cost'] ?? 0, round($responseTime));

            return $response;

        } catch (\Exception $e) {
            Log::error('AI Chat request failed', [
                'provider_id' => $providerId,
                'model_id' => $modelId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Mesaj gönderilemedi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Stream mesaj gönder
     */
    public function streamMessage(int $providerId, int $modelId, string $message, array $context = []): array
    {
        $provider = AiProvider::find($providerId);
        $model = AiProviderModel::find($modelId);

        if (!$provider || !$model) {
            return [
                'success' => false,
                'message' => 'Provider veya model bulunamadı'
            ];
        }

        if (!$provider->isActive()) {
            return [
                'success' => false,
                'message' => 'Provider aktif değil'
            ];
        }

        if (!$model->isAvailable()) {
            return [
                'success' => false,
                'message' => 'Model kullanılabilir değil'
            ];
        }

        if (!$model->supportsStreaming()) {
            return [
                'success' => false,
                'message' => 'Bu model streaming desteklemiyor'
            ];
        }

        $startTime = microtime(true);

        try {
            $response = $this->sendStreamRequestToProvider($provider, $model, $message, $context);
            
            $responseTime = (microtime(true) - $startTime) * 1000;
            
            // Kullanım logunu kaydet
            $this->logUsage($providerId, $modelId, $response['tokens'] ?? 0, $response['cost'] ?? 0, round($responseTime));

            return $response;

        } catch (\Exception $e) {
            Log::error('AI Chat stream request failed', [
                'provider_id' => $providerId,
                'model_id' => $modelId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Stream mesaj gönderilemedi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Yanıt al
     */
    public function getResponse(int $providerId, int $modelId, string $prompt): array
    {
        return $this->sendMessage($providerId, $modelId, $prompt);
    }

    /**
     * Kullanım logunu kaydet
     */
    public function logUsage(int $providerId, int $modelId, int $tokens, float $cost, int $responseTime): void
    {
        try {
            AiProviderUsageLog::create([
                'provider_id' => $providerId,
                'model_id' => $modelId,
                'tokens_used' => $tokens,
                'cost' => $cost,
                'response_time' => $responseTime,
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log AI usage', [
                'provider_id' => $providerId,
                'model_id' => $modelId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * OpenAI Embedding API
     */
    public function getEmbedding(AiProvider $provider, AiProviderModel $model, string $text): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $provider->getApiKey(),
            'Content-Type' => 'application/json'
        ])->post($provider->getBaseUrl() ?: 'https://api.openai.com/v1/embeddings', [
            'model' => $model->model_name,
            'input' => $text
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'success' => true,
                'embedding' => $data['data'][0]['embedding'] ?? null,
                'tokens' => $data['usage']['total_tokens'] ?? 0,
            ];
        }

        return [
            'success' => false,
            'message' => $response->body()
        ];
    }

    /**
     * Provider'a istek gönder
     */
    private function sendRequestToProvider(AiProvider $provider, AiProviderModel $model, string $message, array $context = []): array
    {
        switch ($provider->provider_type) {
            case 'openai':
                return $this->sendOpenAiRequest($provider, $model, $message, $context);
            case 'claude':
                return $this->sendClaudeRequest($provider, $model, $message, $context);
            case 'xai':
                return $this->sendXaiRequest($provider, $model, $message, $context);
            case 'deepseek':
                return $this->sendDeepSeekRequest($provider, $model, $message, $context);
            case 'gemini':
                return $this->sendGeminiRequest($provider, $model, $message, $context);
            case 'openrouter':
                return $this->sendOpenRouterRequest($provider, $model, $message, $context);
            case 'custom':
                return $this->sendCustomRequest($provider, $model, $message, $context);
            default:
                throw new \Exception('Desteklenmeyen provider tipi');
        }
    }

    /**
     * Provider'a stream istek gönder
     */
    private function sendStreamRequestToProvider(AiProvider $provider, AiProviderModel $model, string $message, array $context = []): array
    {
        switch ($provider->provider_type) {
            case 'openai':
                return $this->sendOpenAiStreamRequest($provider, $model, $message, $context);
            case 'claude':
                return $this->sendClaudeStreamRequest($provider, $model, $message, $context);
            case 'openrouter':
                return $this->sendOpenRouterStreamRequest($provider, $model, $message, $context);
            default:
                throw new \Exception('Bu provider için streaming desteklenmiyor');
        }
    }

    // Private methods for different providers
    private function sendOpenAiRequest(AiProvider $provider, AiProviderModel $model, string $message, array $context = []): array
    {
        $messages = [];
        
        // Context'i ekle
        if (!empty($context['system'])) {
            $messages[] = ['role' => 'system', 'content' => $context['system']];
        }
        
        // Conversation history'i ekle
        if (!empty($context['history'])) {
            $messages = array_merge($messages, $context['history']);
        }
        
        $messages[] = ['role' => 'user', 'content' => $message];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $provider->getApiKey(),
            'Content-Type' => 'application/json'
        ])->post($provider->getBaseUrl() ?: 'https://api.openai.com/v1/chat/completions', [
            'model' => $model->model_name,
            'messages' => $messages,
            'max_tokens' => $context['max_tokens'] ?? 1000,
            'temperature' => $context['temperature'] ?? 0.7
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? '';
            $tokens = $data['usage']['total_tokens'] ?? 0;
            $cost = $model->calculateCost($tokens);

            return [
                'success' => true,
                'content' => $content,
                'tokens' => $tokens,
                'cost' => $cost
            ];
        }

        throw new \Exception('OpenAI request failed: ' . $response->body());
    }

    private function sendClaudeRequest(AiProvider $provider, AiProviderModel $model, string $message, array $context = []): array
    {
        $messages = [];
        
        // Context'i ekle
        if (!empty($context['system'])) {
            $messages[] = ['role' => 'user', 'content' => $context['system']];
        }
        
        // Conversation history'i ekle
        if (!empty($context['history'])) {
            $messages = array_merge($messages, $context['history']);
        }
        
        $messages[] = ['role' => 'user', 'content' => $message];

        $response = Http::withHeaders([
            'x-api-key' => $provider->getApiKey(),
            'Content-Type' => 'application/json',
            'anthropic-version' => '2023-06-01'
        ])->post($provider->getBaseUrl() ?: 'https://api.anthropic.com/v1/messages', [
            'model' => $model->model_name,
            'max_tokens' => $context['max_tokens'] ?? 1000,
            'messages' => $messages
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $content = $data['content'][0]['text'] ?? '';
            $tokens = $data['usage']['input_tokens'] + $data['usage']['output_tokens'] ?? 0;
            $cost = $model->calculateCost($tokens);

            return [
                'success' => true,
                'content' => $content,
                'tokens' => $tokens,
                'cost' => $cost
            ];
        }

        throw new \Exception('Claude request failed: ' . $response->body());
    }

    private function sendXaiRequest(AiProvider $provider, AiProviderModel $model, string $message, array $context = []): array
    {
        $messages = [];
        
        // Context'i ekle
        if (!empty($context['system'])) {
            $messages[] = ['role' => 'system', 'content' => $context['system']];
        }
        
        // Conversation history'i ekle
        if (!empty($context['history'])) {
            $messages = array_merge($messages, $context['history']);
        }
        
        $messages[] = ['role' => 'user', 'content' => $message];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $provider->getApiKey(),
            'Content-Type' => 'application/json'
        ])->post($provider->getBaseUrl() ?: 'https://api.x.ai/v1/chat/completions', [
            'model' => $model->model_name,
            'messages' => $messages,
            'max_tokens' => $context['max_tokens'] ?? 1000,
            'temperature' => $context['temperature'] ?? 0.7
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? '';
            $tokens = $data['usage']['total_tokens'] ?? 0;
            $cost = $model->calculateCost($tokens);

            return [
                'success' => true,
                'content' => $content,
                'tokens' => $tokens,
                'cost' => $cost
            ];
        }

        throw new \Exception('xAI request failed: ' . $response->body());
    }

    private function sendDeepSeekRequest(AiProvider $provider, AiProviderModel $model, string $message, array $context = []): array
    {
        $messages = [];
        
        // Context'i ekle
        if (!empty($context['system'])) {
            $messages[] = ['role' => 'system', 'content' => $context['system']];
        }
        
        // Conversation history'i ekle
        if (!empty($context['history'])) {
            $messages = array_merge($messages, $context['history']);
        }
        
        $messages[] = ['role' => 'user', 'content' => $message];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $provider->getApiKey(),
            'Content-Type' => 'application/json'
        ])->post($provider->getBaseUrl() ?: 'https://api.deepseek.com/v1/chat/completions', [
            'model' => $model->model_name,
            'messages' => $messages,
            'max_tokens' => $context['max_tokens'] ?? 1000,
            'temperature' => $context['temperature'] ?? 0.7
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? '';
            $tokens = $data['usage']['total_tokens'] ?? 0;
            $cost = $model->calculateCost($tokens);

            return [
                'success' => true,
                'content' => $content,
                'tokens' => $tokens,
                'cost' => $cost
            ];
        }

        throw new \Exception('DeepSeek request failed: ' . $response->body());
    }

    private function sendGeminiRequest(AiProvider $provider, AiProviderModel $model, string $message, array $context = []): array
    {
        $contents = [];
        
        // Context'i ekle
        if (!empty($context['system'])) {
            $contents[] = ['parts' => [['text' => $context['system']]]];
        }
        
        $contents[] = ['parts' => [['text' => $message]]];

        // Gemini API endpoint'i
        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model->model_name . ':generateContent';
        
        $response = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->post($endpoint . '?key=' . $provider->getApiKey(), [
            'contents' => $contents,
            'generationConfig' => [
                'maxOutputTokens' => $context['max_tokens'] ?? 1000,
                'temperature' => $context['temperature'] ?? 0.7
            ]
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $tokens = $data['usageMetadata']['totalTokenCount'] ?? 0;
            $cost = $model->calculateCost($tokens);

            return [
                'success' => true,
                'content' => $content,
                'tokens' => $tokens,
                'cost' => $cost
            ];
        }

        $errorData = $response->json();
        $errorMessage = 'Gemini isteği başarısız';
        
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
                    $errorMessage = 'Gemini isteği başarısız: ' . ($errorData['error']['message'] ?? 'Bilinmeyen hata');
            }
        }
        
        throw new \Exception($errorMessage);
    }

    private function sendCustomRequest(AiProvider $provider, AiProviderModel $model, string $message, array $context = []): array
    {
        if (!$provider->getBaseUrl()) {
            throw new \Exception('Custom provider için base URL gerekli');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $provider->getApiKey(),
            'Content-Type' => 'application/json'
        ])->post($provider->getBaseUrl(), [
            'model' => $model->model_name,
            'message' => $message,
            'context' => $context
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $content = $data['content'] ?? $data['response'] ?? '';
            $tokens = $data['tokens'] ?? 0;
            $cost = $model->calculateCost($tokens);

            return [
                'success' => true,
                'content' => $content,
                'tokens' => $tokens,
                'cost' => $cost
            ];
        }

        throw new \Exception('Custom provider request failed: ' . $response->body());
    }

    // Stream request methods
    private function sendOpenAiStreamRequest(AiProvider $provider, AiProviderModel $model, string $message, array $context = []): array
    {
        $messages = [];
        
        if (!empty($context['system'])) {
            $messages[] = ['role' => 'system', 'content' => $context['system']];
        }
        
        if (!empty($context['history'])) {
            $messages = array_merge($messages, $context['history']);
        }
        
        $messages[] = ['role' => 'user', 'content' => $message];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $provider->getApiKey(),
            'Content-Type' => 'application/json'
        ])->post($provider->getBaseUrl() ?: 'https://api.openai.com/v1/chat/completions', [
            'model' => $model->model_name,
            'messages' => $messages,
            'max_tokens' => $context['max_tokens'] ?? 1000,
            'temperature' => $context['temperature'] ?? 0.7,
            'stream' => true
        ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'stream' => $response->body(),
                'tokens' => 0, // Stream'de token sayısı hesaplanamıyor
                'cost' => 0
            ];
        }

        throw new \Exception('OpenAI stream request failed: ' . $response->body());
    }

    private function sendClaudeStreamRequest(AiProvider $provider, AiProviderModel $model, string $message, array $context = []): array
    {
        $messages = [];
        
        if (!empty($context['system'])) {
            $messages[] = ['role' => 'user', 'content' => $context['system']];
        }
        
        if (!empty($context['history'])) {
            $messages = array_merge($messages, $context['history']);
        }
        
        $messages[] = ['role' => 'user', 'content' => $message];

        $response = Http::withHeaders([
            'x-api-key' => $provider->getApiKey(),
            'Content-Type' => 'application/json',
            'anthropic-version' => '2023-06-01'
        ])->post($provider->getBaseUrl() ?: 'https://api.anthropic.com/v1/messages', [
            'model' => $model->model_name,
            'max_tokens' => $context['max_tokens'] ?? 1000,
            'messages' => $messages,
            'stream' => true
        ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'stream' => $response->body(),
                'tokens' => 0,
                'cost' => 0
            ];
        }

        throw new \Exception('Claude stream request failed: ' . $response->body());
    }

    private function sendOpenRouterRequest(AiProvider $provider, AiProviderModel $model, string $message, array $context = []): array
    {
        $messages = [];
        
        // Context'i ekle
        if (!empty($context['system'])) {
            $messages[] = ['role' => 'system', 'content' => $context['system']];
        }
        
        // Conversation history'i ekle
        if (!empty($context['history'])) {
            $messages = array_merge($messages, $context['history']);
        }
        
        $messages[] = ['role' => 'user', 'content' => $message];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $provider->getApiKey(),
            'Content-Type' => 'application/json'
        ])->post($provider->getBaseUrl() . '/chat/completions', [
            'model' => $model->model_name,
            'messages' => $messages,
            'max_tokens' => $context['max_tokens'] ?? 1000,
            'temperature' => $context['temperature'] ?? 0.7
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? '';
            $tokens = $data['usage']['total_tokens'] ?? 0;
            $cost = $model->calculateCost($tokens);

            return [
                'success' => true,
                'content' => $content,
                'tokens' => $tokens,
                'cost' => $cost
            ];
        }

        throw new \Exception('OpenRouter request failed: ' . $response->body());
    }

    private function sendOpenRouterStreamRequest(AiProvider $provider, AiProviderModel $model, string $message, array $context = []): array
    {
        $messages = [];
        
        if (!empty($context['system'])) {
            $messages[] = ['role' => 'system', 'content' => $context['system']];
        }
        
        if (!empty($context['history'])) {
            $messages = array_merge($messages, $context['history']);
        }
        
        $messages[] = ['role' => 'user', 'content' => $message];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $provider->getApiKey(),
            'Content-Type' => 'application/json'
        ])->post($provider->getBaseUrl() . '/chat/completions', [
            'model' => $model->model_name,
            'messages' => $messages,
            'max_tokens' => $context['max_tokens'] ?? 1000,
            'temperature' => $context['temperature'] ?? 0.7,
            'stream' => true
        ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'stream' => $response->body(),
                'tokens' => 0, // Stream'de token sayısı hesaplanamıyor
                'cost' => 0
            ];
        }

        throw new \Exception('OpenRouter stream request failed: ' . $response->body());
    }
} 