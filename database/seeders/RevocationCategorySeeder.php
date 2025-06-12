<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RevocationCategory;

class RevocationCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RevocationCategory::create([
            'code' => 'D02',
            'name' => 'Revocatoria D02',
            'processor_percentage' => 55,
            'client_percentage' => 65,
            'observations' => '',
            'is_active' => true
        ]);

        RevocationCategory::create([
            'code' => 'C29',
            'name' => 'Revocatoria C29',
            'processor_percentage' => 45,
            'client_percentage' => 60,
            'observations' => '',
            'is_active' => true
        ]);
    }
}
