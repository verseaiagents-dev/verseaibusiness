<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\AgentModel;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the default agent model
        $defaultModel = AgentModel::where('default', true)->first();
        
        if (!$defaultModel) {
            $this->command->error('No default agent model found. Please run AgentModelSeeder first.');
            return;
        }

        // Get all users
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->info('No users found. Creating sample agents for test user.');
            
            // Create a test user if none exists
            $user = User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
                'role' => 'user',
                'plan_type' => 'free',
                'token_balance' => 50,
                'sector' => 'E-commerce',
            ]);
        } else {
            $user = $users->first();
        }

        // Create sample agents for different sectors
        $agents = [
            [
                'user_id' => $user->id,
                'role_name' => 'Customer Support Agent',
                'sector' => 'E-commerce',
                'training_data' => [
                    'company_policies' => 'Our return policy allows 30-day returns with original receipt',
                    'product_knowledge' => 'We sell electronics, clothing, home goods, and accessories',
                    'communication_style' => 'Friendly, professional, and helpful',
                    'common_questions' => [
                        'How do I track my order?',
                        'What is your return policy?',
                        'Do you ship internationally?',
                        'How can I contact customer service?'
                    ]
                ],
                'model_id' => $defaultModel->id,
                'status' => 'active',
                'usage_limit' => 1000,
            ],
            [
                'user_id' => $user->id,
                'role_name' => 'Restaurant Assistant',
                'sector' => 'Food & Beverage',
                'training_data' => [
                    'restaurant_info' => 'We are a family-owned restaurant serving Mediterranean cuisine',
                    'menu_items' => 'Our specialties include grilled lamb, fresh seafood, and homemade desserts',
                    'operating_hours' => 'Open Tuesday-Sunday, 11:00 AM - 10:00 PM',
                    'reservation_policy' => 'Reservations recommended for dinner, walk-ins welcome for lunch',
                    'common_questions' => [
                        'What are your opening hours?',
                        'Do you take reservations?',
                        'Is there parking available?',
                        'Do you have vegetarian options?'
                    ]
                ],
                'model_id' => $defaultModel->id,
                'status' => 'active',
                'usage_limit' => 500,
            ],
            [
                'user_id' => $user->id,
                'role_name' => 'Real Estate Consultant',
                'sector' => 'Real Estate',
                'training_data' => [
                    'company_info' => 'We are a leading real estate agency specializing in residential properties',
                    'service_areas' => 'We serve the greater metropolitan area and surrounding suburbs',
                    'property_types' => 'Single-family homes, condos, townhouses, and investment properties',
                    'process_info' => 'We offer free property valuations, market analysis, and buyer representation',
                    'common_questions' => [
                        'What is the current market value of my home?',
                        'How long does it take to sell a property?',
                        'What are the closing costs?',
                        'Do you offer virtual tours?'
                    ]
                ],
                'model_id' => $defaultModel->id,
                'status' => 'active',
                'usage_limit' => 750,
            ],
            [
                'user_id' => $user->id,
                'role_name' => 'Hotel Concierge',
                'sector' => 'Hospitality',
                'training_data' => [
                    'hotel_info' => 'We are a luxury 4-star hotel in the city center',
                    'amenities' => 'Free WiFi, fitness center, spa, restaurant, and business center',
                    'room_types' => 'Standard, Deluxe, Suite, and Executive rooms available',
                    'local_attractions' => 'We are walking distance to museums, shopping, and restaurants',
                    'common_questions' => [
                        'What time is check-in and check-out?',
                        'Do you have airport shuttle service?',
                        'Is breakfast included?',
                        'Can you recommend local restaurants?'
                    ]
                ],
                'model_id' => $defaultModel->id,
                'status' => 'inactive',
                'usage_limit' => 300,
            ],
        ];

        foreach ($agents as $agentData) {
            Agent::create($agentData);
        }

        $this->command->info('Sample agents created successfully!');
    }
}
