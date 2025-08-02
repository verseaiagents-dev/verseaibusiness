<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AiProvider;
use App\Services\AiProviderService;
use App\Services\AiChatService;

class TestOpenRouter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:openrouter {--api-key=} {--model=openai/gpt-4o}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test OpenRouter integration';

    /**
     * Execute the console command.
     */
    public function handle(AiProviderService $providerService, AiChatService $chatService)
    {
        $this->info('Testing OpenRouter Integration...');

        // OpenRouter provider'ını bul veya oluştur
        $provider = AiProvider::where('provider_type', 'openrouter')->first();

        if (!$provider) {
            $this->error('OpenRouter provider bulunamadı. Önce seeder çalıştırın.');
            return 1;
        }

        // API key'i güncelle
        $apiKey = $this->option('api-key');
        if ($apiKey) {
            $provider->api_key = $apiKey;
            $provider->save();
            $this->info('API key güncellendi.');
        }

        if (!$provider->hasApiKey()) {
            $this->error('API key yapılandırılmamış. --api-key parametresi ile API key ekleyin.');
            return 1;
        }

        // Bağlantı testi
        $this->info('Bağlantı testi yapılıyor...');
        $testResult = $providerService->testProviderConnection($provider->id);

        if ($testResult['success']) {
            $this->info('✅ ' . $testResult['message']);
            $this->info('Response time: ' . $testResult['response_time'] . 'ms');
        } else {
            $this->error('❌ ' . $testResult['message']);
            return 1;
        }

        // Model senkronizasyonu
        $this->info('Modeller senkronize ediliyor...');
        $syncResult = $providerService->syncProviderModels($provider->id);

        if ($syncResult['success']) {
            $this->info('✅ ' . $syncResult['message']);
        } else {
            $this->error('❌ ' . $syncResult['message']);
        }

        // Chat testi
        $this->info('Chat testi yapılıyor...');
        $model = $provider->models()->where('model_name', $this->option('model'))->first();

        if (!$model) {
            $this->error('Model bulunamadı: ' . $this->option('model'));
            return 1;
        }

        $chatResult = $chatService->sendMessage(
            $provider->id,
            $model->id,
            'Merhaba! Sen kimsin ve ne yapabilirsin?'
        );

        if ($chatResult['success']) {
            $this->info('✅ Chat testi başarılı!');
            $this->info('Response: ' . $chatResult['content']);
            $this->info('Tokens: ' . $chatResult['tokens']);
            $this->info('Cost: $' . $chatResult['cost']);
        } else {
            $this->error('❌ Chat testi başarısız: ' . $chatResult['message']);
            return 1;
        }

        $this->info('🎉 OpenRouter entegrasyonu başarıyla test edildi!');
        return 0;
    }
}
