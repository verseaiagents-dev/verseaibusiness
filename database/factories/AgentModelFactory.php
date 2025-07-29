<?php

namespace Database\Factories;

use App\Models\AgentModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AgentModel>
 */
class AgentModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $models = [
            'GPT-4' => [
                'api_keys' => ['openai_api_key' => 'fake-openai-key'],
                'model_parameters' => ['temperature' => 0.7, 'max_tokens' => 2048]
            ],
            'Claude-3' => [
                'api_keys' => ['anthropic_api_key' => 'fake-anthropic-key'],
                'model_parameters' => ['temperature' => 0.8, 'max_tokens' => 4096]
            ],
            'Gemini Pro' => [
                'api_keys' => ['google_api_key' => 'fake-google-key'],
                'model_parameters' => ['temperature' => 0.6, 'max_tokens' => 3072]
            ],
            'Llama-2' => [
                'api_keys' => ['meta_api_key' => 'fake-meta-key'],
                'model_parameters' => ['temperature' => 0.5, 'max_tokens' => 1024]
            ]
        ];

        $modelName = $this->faker->randomElement(array_keys($models));
        $modelData = $models[$modelName];

        return [
            'model_name' => $modelName,
            'api_keys' => $modelData['api_keys'],
            'model_parameters' => $modelData['model_parameters'],
            'default' => false,
        ];
    }

    /**
     * Indicate that the model is the default.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'default' => true,
        ]);
    }
}
