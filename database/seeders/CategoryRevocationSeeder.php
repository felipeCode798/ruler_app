<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CategoryRevocation;

class CategoryRevocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CategoryRevocation::create([
            'name' => 'A',
            'comparing_value' => 152672,
            'comparing_value_discount' => 76336,
            'fee_value' => 50000,
            'transit_value' => 57252,
            'cia_value' => 21184,
            'cia_discount_value' => 20,
            'cia_total_value' => 16948,
            'is_active' => true,
            'slug' => 'A',
        ]);

    }
}
