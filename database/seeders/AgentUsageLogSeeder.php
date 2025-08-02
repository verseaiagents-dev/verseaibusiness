<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AgentUsageLog;
use App\Models\Agent;
use App\Models\User;
use Carbon\Carbon;

class AgentUsageLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $agents = Agent::all();
        $users = User::all();

        if ($agents->isEmpty() || $users->isEmpty()) {
            $this->command->info('No agents or users found. Skipping usage log seeding.');
            return;
        }

        $providers = ['versai', 'openai', 'anthropic'];
        $models = [
            'versai' => ['versai-gpt-4', 'versai-gpt-3.5'],
            'openai' => ['gpt-4', 'gpt-3.5-turbo'],
            'anthropic' => ['claude-3-opus', 'claude-3-sonnet']
        ];

        // Son 30 gün için test verileri oluştur
        for ($i = 0; $i < 100; $i++) {
            $agent = $agents->random();
            $user = $users->random();
            $provider = $providers[array_rand($providers)];
            $model = $models[$provider][array_rand($models[$provider])];
            
            // Rastgele token sayıları
            $inputTokens = rand(100, 2000);
            $outputTokens = rand(50, 1000);
            
            // Provider bazında fiyatlandırma (gerçek fiyatlara yakın)
            $inputCostPer1K = 0;
            $outputCostPer1K = 0;
            
            switch ($provider) {
                case 'versai':
                    $inputCostPer1K = 0.03;
                    $outputCostPer1K = 0.06;
                    break;
                case 'openai':
                    if (str_contains($model, 'gpt-4')) {
                        $inputCostPer1K = 0.03;
                        $outputCostPer1K = 0.06;
                    } else {
                        $inputCostPer1K = 0.0015;
                        $outputCostPer1K = 0.002;
                    }
                    break;
                case 'anthropic':
                    $inputCostPer1K = 0.015;
                    $outputCostPer1K = 0.075;
                    break;
            }
            
            $inputCost = ($inputTokens / 1000) * $inputCostPer1K;
            $outputCost = ($outputTokens / 1000) * $outputCostPer1K;
            $totalCost = $inputCost + $outputCost;
            
            // Rastgele tarih (son 30 gün içinde)
            $randomDays = rand(0, 30);
            $createdAt = Carbon::now()->subDays($randomDays);
            
            AgentUsageLog::create([
                'agent_id' => $agent->id,
                'user_id' => $user->id,
                'provider' => $provider,
                'model' => $model,
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'input_cost' => $inputCost,
                'output_cost' => $outputCost,
                'total_cost' => $totalCost,
                'currency' => 'USD',
                'metadata' => [
                    'request_id' => 'req_' . uniqid(),
                    'response_time' => rand(500, 3000),
                    'status' => 'success'
                ],
                'created_at' => $createdAt,
                'updated_at' => $createdAt
            ]);
        }

        $this->command->info('Agent usage logs seeded successfully!');
    }
}
