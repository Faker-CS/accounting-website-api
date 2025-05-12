<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users=[
            [
                'name'=>'comptable',
                'email'=>"",
                'password'=>Hash::make('password'),
            ],
            [
                'name'=>'Jane Doe',
                'email'=>"",
                'password'=>Hash::make('password'),
            ],
            [
                'name'=>'Alice Smith',
                'email'=>"",
                'password'=>Hash::make('password'),
            ],
            [
                'name'=>'Bob Johnson',
                'email'=>"",
                'password'=>Hash::make('password'),
            ],
        ];

        $roles=['comptable','admin','user'];
        foreach ($users as $user) {
           $userR= User::create($user);
            $userR->assignRole($roles['comptable']);

        }
    }
}
