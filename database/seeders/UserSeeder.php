<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Faker sassi user
        $user = User::create([
            'name' => 'Faker sassi',
            'email' => 'faker.sassi@gmail.com',
            'password' => Hash::make('password123'),
            'phoneNumber' => '+216 22 333 444',
        ]);

        // Assign comptable role
        $user->assignRole('comptable');
    }
} 