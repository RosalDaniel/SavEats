<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates a default admin user only if it does not already exist.
     */
    public function run(): void
    {
        $adminEmail = 'admin@saveats.com';
        
        // Check if admin user already exists
        $existingAdmin = User::where('email', $adminEmail)->first();
        
        if (!$existingAdmin) {
            User::create([
                'name' => 'Admin',
                'email' => $adminEmail,
                'password' => Hash::make('SaveatsAdmin2024!'),
                'role' => 'admin',
            ]);
            
            $this->command->info('Admin user created successfully!');
            $this->command->info('Email: ' . $adminEmail);
            $this->command->warn('Password: SaveatsAdmin2024!');
            $this->command->warn('Please change the password in production!');
        } else {
            $this->command->info('Admin user already exists. Skipping creation.');
        }
    }
}
