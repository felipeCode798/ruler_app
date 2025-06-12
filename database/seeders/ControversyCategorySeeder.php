<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ControversyCategory;

class ControversyCategorySeeder extends Seeder
{
    public function run(): void
    {
        ControversyCategory::create([
            'code' => 'C',
            'processor_value' => 280000,
            'client_value' => 310000,
            'is_active' => true
        ]);

        ControversyCategory::create([
            'code' => 'D',
            'processor_value' => 450000,
            'client_value' => 530000,
            'is_active' => true
        ]);
    }
}
