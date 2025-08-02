<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create profiles for existing users
        $users = User::whereDoesntHave('profile')->get();
        
        foreach ($users as $user) {
            UserProfile::factory()->create([
                'user_id' => $user->id,
                'business_name' => $user->name,
                'profile_slug' => UserProfile::generateProfileSlug($user->name),
                'industry' => $user->sector ?? 'e-ticaret',
                'is_public' => true,
            ]);
        }

        // Create some sample profiles for testing
        UserProfile::factory(10)->create();
        
        // Create some public profiles
        UserProfile::factory(5)->public()->active()->create();
        
        // Create some private profiles
        UserProfile::factory(3)->private()->create();
        
        // Create some inactive profiles
        UserProfile::factory(2)->inactive()->create();
    }
}
