<?php

namespace Database\Factories;

use App\Models\Agent;
use App\Models\AgentModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Agent>
 */
class AgentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sectors = ['E-commerce', 'Food & Beverage', 'Real Estate', 'Hospitality', 'Healthcare', 'Education', 'Technology', 'Finance'];
        $roles = [
            'Customer Support Agent',
            'Sales Assistant',
            'Technical Support',
            'Consultant',
            'Concierge',
            'Advisor',
            'Assistant',
            'Helper'
        ];

        return [
            'user_id' => User::factory(),
            'role_name' => $this->faker->randomElement($roles),
            'sector' => $this->faker->randomElement($sectors),
            'training_data' => [
                'company_info' => $this->faker->paragraph(),
                'product_knowledge' => $this->faker->paragraph(),
                'communication_style' => $this->faker->sentence(),
                'common_questions' => [
                    $this->faker->sentence(),
                    $this->faker->sentence(),
                    $this->faker->sentence(),
                ]
            ],
            'model_id' => AgentModel::factory(),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'usage_limit' => $this->faker->optional()->numberBetween(100, 2000),
        ];
    }

    /**
     * Indicate that the agent is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the agent is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
