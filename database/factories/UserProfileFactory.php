<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserProfile>
 */
class UserProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $businessName = $this->faker->company();
        
        return [
            'user_id' => User::factory(),
            'username' => $this->faker->unique()->userName(),
            'business_name' => $businessName,
            'profile_slug' => UserProfile::generateProfileSlug($businessName),
            'avatar_url' => $this->faker->imageUrl(200, 200, 'business'),
            'bio' => $this->faker->paragraph(2),
            'industry' => $this->faker->randomElement(['e-ticaret', 'turizm', 'emlak', 'restoran', 'eğitim', 'sağlık', 'teknoloji']),
            'location' => $this->faker->city() . ', ' . $this->faker->country(),
            'total_sessions' => $this->faker->numberBetween(0, 1000),
            'total_events_tracked' => $this->faker->numberBetween(0, 5000),
            'conversion_rate' => $this->faker->randomFloat(2, 0, 25),
            'popular_topics' => $this->faker->randomElements(['Fiyat', 'Teslimat', 'İade', 'Kalite', 'Hizmet', 'Garanti'], $this->faker->numberBetween(2, 5)),
            'response_quality_score' => $this->faker->numberBetween(60, 100),
            'reviews_count' => $this->faker->numberBetween(0, 100),
            'average_rating' => $this->faker->randomFloat(1, 3.5, 5.0),
            'featured_testimonials' => [
                [
                    'name' => $this->faker->name(),
                    'rating' => $this->faker->numberBetween(4, 5),
                    'comment' => $this->faker->paragraph(1),
                    'date' => $this->faker->dateTimeThisYear()->format('Y-m-d')
                ],
                [
                    'name' => $this->faker->name(),
                    'rating' => $this->faker->numberBetween(4, 5),
                    'comment' => $this->faker->paragraph(1),
                    'date' => $this->faker->dateTimeThisYear()->format('Y-m-d')
                ]
            ],
            'share_qr_code_url' => $this->faker->imageUrl(200, 200, 'qr'),
            'website_url' => $this->faker->url(),
            'social_links' => [
                'instagram' => $this->faker->optional()->url(),
                'linkedin' => $this->faker->optional()->url(),
                'facebook' => $this->faker->optional()->url(),
                'twitter' => $this->faker->optional()->url(),
            ],
            'contact_email' => $this->faker->optional()->email(),
            'is_public' => $this->faker->boolean(80), // 80% chance of being public
            'last_active_at' => $this->faker->dateTimeThisMonth(),
        ];
    }

    /**
     * Indicate that the profile is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * Indicate that the profile is private.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }

    /**
     * Indicate that the profile is active (recent activity).
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_active_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Indicate that the profile is inactive (old activity).
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_active_at' => $this->faker->dateTimeBetween('-6 months', '-1 month'),
        ]);
    }
}
