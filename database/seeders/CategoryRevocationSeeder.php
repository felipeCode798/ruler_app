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
            'code' => 'A',
            'smld_value' => 4,
            'subpoena_value' => 161150,
            'cia_value_50' => 22200,
            'transit_pay_50' => 80575,
            'total_discount_50' => 60475,
            'cia_value_20' => 32300,
            'transit_pay_20' => 120863,
            'total_discount_20' => 7987,
            'standard_value' => 40000,
            'is_active' => true
        ]);

        CategoryRevocation::create([
            'code' => 'B',
            'smld_value' => 8,
            'subpoena_value' => 321839,
            'cia_value_50' => 42300,
            'transit_pay_50' => 160920,
            'total_discount_50' => 120719,
            'cia_value_20' => 62400,
            'transit_pay_20' => 241379,
            'total_discount_20' => 18060,
            'standard_value' => 40000,
            'is_active' => true
        ]);

    }
}
