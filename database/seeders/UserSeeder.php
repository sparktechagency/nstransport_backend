<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'System Administrator',
            'role' => 'Admin',
            'passcode' =>'252025',
            'email' => 'admin@gmail.com',
            'email_verified_at'=>now(),
            'password' => Hash::make('123456'),
        ]);
        User::create([
            'name' => 'System User',
            'role' => 'User',
            'passcode' =>'120110',
            'email' => 'user@gmail.com',
            'email_verified_at'=>now(),
            'password' => Hash::make('123456'),
        ]);
    }
}