<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin user already exists
        $existingAdmin = User::where('email', 'kadirdurmazlar@gmail.com')->first();
        
        if (!$existingAdmin) {
            User::create([
                'name' => 'Kadir Durmazlar',
                'email' => 'kadirdurmazlar@gmail.com',
                'password' => Hash::make('Copperage.26'),
                'role' => 'admin',
                'plan_type' => 'premium',
                'token_balance' => 10000,
                'email_verified_at' => now(),
            ]);
            
            $this->command->info('Admin user created successfully!');
            $this->command->info('Email: kadirdurmazlar@gmail.com');
            $this->command->info('Password: Copperage.26');
        } else {
            $this->command->info('Admin user already exists!');
        }
    }
}
