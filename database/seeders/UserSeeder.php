<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Crear usuario admin
        $admin = User::create([
            'name' => 'felipe',
            'email' => 'felipe@gmail.com',
            'password' => Hash::make('123456789'),
            'phone' => '1234567890',
            'dni' => '123456789',
        ]);
        $admin->assignRole('admin');

        // Crear usuario editor
        $editor = User::create([
            'name' => 'camila',
            'email' => 'camila@gmail.com',
            'password' => Hash::make('123456789'),
            'phone' => '0987654321',
            'dni' => '987654321',
        ]);
        $editor->assignRole('editor');
    }
}
