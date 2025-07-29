<?php

namespace Database\Seeders;

use App\Models\AgentModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AgentModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default agent models
        AgentModel::create([
            'model_name' => 'GPT-4',
            'api_keys' => [
                'openai_api_key' => env('OPENAI_API_KEY', 'fake-openai-key'),
                'organization_id' => env('OPENAI_ORG_ID', 'fake-org-id'),
            ],
            'model_parameters' => [
                'temperature' => 0.7,
                'max_tokens' => 2048,
                'top_p' => 1.0,
            ],
            'default' => true,
        ]);

        AgentModel::create([
            'model_name' => 'Claude-3',
            'api_keys' => [
                'anthropic_api_key' => env('ANTHROPIC_API_KEY', 'fake-anthropic-key'),
            ],
            'model_parameters' => [
                'temperature' => 0.8,
                'max_tokens' => 4096,
                'top_p' => 0.9,
            ],
            'default' => false,
        ]);

        AgentModel::create([
            'model_name' => 'Gemini Pro',
            'api_keys' => [
                'google_api_key' => env('GOOGLE_API_KEY', 'fake-google-key'),
            ],
            'model_parameters' => [
                'temperature' => 0.6,
                'max_tokens' => 3072,
                'top_p' => 0.95,
            ],
            'default' => false,
        ]);

        $this->command->info('Agent models seeded successfully!');
    }
}
