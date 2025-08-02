<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AiProvider;
use App\Models\AiProviderModel;

class AiProviderModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $models = [
            'openai' => [
                [
                    'model_name' => 'gpt-4',
                    'display_name' => 'GPT-4',
                    'is_available' => true,
                    'max_tokens' => 8192,
                    'cost_per_1k_tokens' => 0.03,
                    'features' => ['chat_completion', 'function_calling']
                ],
                [
                    'model_name' => 'gpt-4-turbo',
                    'display_name' => 'GPT-4 Turbo',
                    'is_available' => true,
                    'max_tokens' => 128000,
                    'cost_per_1k_tokens' => 0.01,
                    'features' => ['chat_completion', 'function_calling', 'vision']
                ],
                [
                    'model_name' => 'gpt-3.5-turbo',
                    'display_name' => 'GPT-3.5 Turbo',
                    'is_available' => true,
                    'max_tokens' => 4096,
                    'cost_per_1k_tokens' => 0.002,
                    'features' => ['chat_completion', 'function_calling']
                ],
                [
                    'model_name' => 'gpt-4o',
                    'display_name' => 'GPT-4o',
                    'is_available' => true,
                    'max_tokens' => 128000,
                    'cost_per_1k_tokens' => 0.005,
                    'features' => ['chat_completion', 'function_calling', 'vision']
                ]
            ],
            'claude' => [
                [
                    'model_name' => 'claude-3-opus-20240229',
                    'display_name' => 'Claude 3 Opus',
                    'is_available' => true,
                    'max_tokens' => 200000,
                    'cost_per_1k_tokens' => 0.015,
                    'features' => ['chat_completion', 'function_calling', 'vision']
                ],
                [
                    'model_name' => 'claude-3-sonnet-20240229',
                    'display_name' => 'Claude 3 Sonnet',
                    'is_available' => true,
                    'max_tokens' => 200000,
                    'cost_per_1k_tokens' => 0.003,
                    'features' => ['chat_completion', 'function_calling', 'vision']
                ],
                [
                    'model_name' => 'claude-3-haiku-20240307',
                    'display_name' => 'Claude 3 Haiku',
                    'is_available' => true,
                    'max_tokens' => 200000,
                    'cost_per_1k_tokens' => 0.00025,
                    'features' => ['chat_completion', 'function_calling', 'vision']
                ]
            ],
            'xai' => [
                [
                    'model_name' => 'grok-beta',
                    'display_name' => 'Grok Beta',
                    'is_available' => true,
                    'max_tokens' => 8192,
                    'cost_per_1k_tokens' => 0.01,
                    'features' => ['chat_completion']
                ]
            ],
            'deepseek' => [
                [
                    'model_name' => 'deepseek-chat',
                    'display_name' => 'DeepSeek Chat',
                    'is_available' => true,
                    'max_tokens' => 32768,
                    'cost_per_1k_tokens' => 0.002,
                    'features' => ['chat_completion']
                ],
                [
                    'model_name' => 'deepseek-coder',
                    'display_name' => 'DeepSeek Coder',
                    'is_available' => true,
                    'max_tokens' => 32768,
                    'cost_per_1k_tokens' => 0.002,
                    'features' => ['chat_completion']
                ]
            ],
            'gemini' => [
                [
                    'model_name' => 'gemini-pro',
                    'display_name' => 'Gemini Pro',
                    'is_available' => true,
                    'max_tokens' => 32768,
                    'cost_per_1k_tokens' => 0.0005,
                    'features' => ['chat_completion']
                ],
                [
                    'model_name' => 'gemini-pro-vision',
                    'display_name' => 'Gemini Pro Vision',
                    'is_available' => true,
                    'max_tokens' => 32768,
                    'cost_per_1k_tokens' => 0.0005,
                    'features' => ['chat_completion', 'vision']
                ]
            ],
            'voyage' => [
                [
                    'model_name' => 'voyage-01',
                    'display_name' => 'Voyage-01',
                    'is_available' => true,
                    'max_tokens' => null,
                    'cost_per_1k_tokens' => 0.0001,
                    'features' => ['embedding']
                ]
            ]
        ];

        foreach ($models as $providerName => $providerModels) {
            $provider = AiProvider::where('name', $providerName)->first();
            
            if ($provider) {
                foreach ($providerModels as $modelData) {
                    AiProviderModel::updateOrCreate(
                        [
                            'provider_id' => $provider->id,
                            'model_name' => $modelData['model_name']
                        ],
                        array_merge($modelData, ['provider_id' => $provider->id])
                    );
                }
            }
        }

        $this->command->info('AI Provider Models seeded successfully!');
    }
} 