<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Lawyer;

class LawyerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Lawyer::create([
            'name' => 'Leandro',
            'phone' => 1234567890,
            'prefix' => 'L',
            'commission' => 13,
            'is_active' => true,
            'slug' => 'leandro',
        ]);
    }
}
