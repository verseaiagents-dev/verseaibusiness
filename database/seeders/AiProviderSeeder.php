<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AiProvider;

class AiProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $providers = [
            [
                'name' => 'openai',
                'display_name' => 'OpenAI',
                'provider_type' => 'openai',
                'api_key' => null, // API key kullanıcı tarafından eklenmeli
                'base_url' => null,
                'default_model' => 'gpt-4',
                'is_active' => true,
                'priority' => 1,
                'settings' => [
                    'timeout' => 60,
                    'max_retries' => 3,
                    'temperature' => 0.7,
                    'max_tokens' => 4000
                ]
            ],
            [
                'name' => 'claude',
                'display_name' => 'Claude (Anthropic)',
                'provider_type' => 'claude',
                'api_key' => null,
                'base_url' => null,
                'default_model' => 'claude-3-sonnet-20240229',
                'is_active' => true,
                'priority' => 2,
                'settings' => [
                    'timeout' => 60,
                    'max_retries' => 3,
                    'max_tokens' => 4000
                ]
            ],
            [
                'name' => 'xai',
                'display_name' => 'xAI (Grok)',
                'provider_type' => 'xai',
                'api_key' => null,
                'base_url' => null,
                'default_model' => 'grok-beta',
                'is_active' => true,
                'priority' => 3,
                'settings' => [
                    'timeout' => 60,
                    'max_retries' => 3,
                    'temperature' => 0.7,
                    'max_tokens' => 4000
                ]
            ],
            [
                'name' => 'deepseek',
                'display_name' => 'DeepSeek',
                'provider_type' => 'deepseek',
                'api_key' => null,
                'base_url' => null,
                'default_model' => 'deepseek-chat',
                'is_active' => true,
                'priority' => 4,
                'settings' => [
                    'timeout' => 60,
                    'max_retries' => 3,
                    'temperature' => 0.7,
                    'max_tokens' => 4000
                ]
            ],
            [
                'name' => 'gemini',
                'display_name' => 'Google Gemini',
                'provider_type' => 'gemini',
                'api_key' => null,
                'base_url' => null,
                'default_model' => 'gemini-pro',
                'is_active' => true,
                'priority' => 5,
                'settings' => [
                    'timeout' => 60,
                    'max_retries' => 3,
                    'temperature' => 0.7,
                    'max_tokens' => 4000
                ]
            ],
            [
                'name' => 'voyage',
                'display_name' => 'Voyage AI',
                'provider_type' => 'voyage',
                'api_key' => null,
                'base_url' => null,
                'default_model' => 'voyage-01',
                'is_active' => true,
                'priority' => 6,
                'settings' => [
                    'timeout' => 60,
                    'max_retries' => 3
                ]
            ],
            [
                'name' => 'openrouter',
                'display_name' => 'OpenRouter',
                'provider_type' => 'openrouter',
                'api_key' => null,
                'base_url' => 'https://openrouter.ai/api/v1',
                'default_model' => 'openai/gpt-4o',
                'is_active' => true,
                'priority' => 7,
                'settings' => [
                    'timeout' => 60,
                    'max_retries' => 3,
                    'temperature' => 0.7,
                    'max_tokens' => 4000,
                    'available_models' => [
                        'openai/gpt-4o',
                        'openai/gpt-4o-mini',
                        'openai/gpt-4-turbo',
                        'openai/gpt-3.5-turbo',
                        'anthropic/claude-3.5-sonnet',
                        'anthropic/claude-3-haiku',
                        'meta-llama/llama-3.1-8b-instruct',
                        'meta-llama/llama-3.1-70b-instruct',
                        'google/gemini-2.0-flash-exp',
                        'google/gemini-2.5-flash-exp',
                        'mistralai/mistral-7b-instruct',
                        'mistralai/mixtral-8x7b-instruct'
                    ]
                ]
            ]
        ];

        foreach ($providers as $providerData) {
            AiProvider::updateOrCreate(
                ['name' => $providerData['name']],
                $providerData
            );
        }

        $this->command->info('AI Providers seeded successfully!');
        $this->command->info('Note: API keys need to be configured manually in the admin panel.');
    }
} 