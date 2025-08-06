<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ComptableUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or get the Comptable role
        $comptableRole = Role::firstOrCreate(['name' => 'Comptable']);

        // Create the user
        $user = User::create([
            'name' => 'Faker Sassi',
            'email' => 'faker@gmail.com',
            'password' => Hash::make('faker123'),
            'phoneNumber' => '1234567890',
            'city' => 'Test City',
            'state' => 'Test State',
            'address' => 'Test Address',
            'zipCode' => '12345',
        ]);

        // Assign the Comptable role to the user
        $user->assignRole($comptableRole);
    }
}
