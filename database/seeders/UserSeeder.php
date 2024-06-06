<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'felipe',
            'email' => 'felipe@gmail.com',
            'password' => Hash::make('123456789'),
            'phone' => '1234567890',
            'address' => '123 Main St',
            'dni' => '123456789',
        ]);

        User::create([
            'name' => 'camila',
            'email' => 'camila@gmail.com',
            'password' => Hash::make('123456789'),
            'phone' => '0987654321',
            'address' => '456 Another St',
            'dni' => '987654321',
        ]);
    }
}
