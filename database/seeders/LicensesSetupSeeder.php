<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LicensesSetupCategory;

class LicensesSetupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LicensesSetupCategory::create([
            'name' => 'A2',
            'price' => '450000',
            'price_renewal' => '150000'
        ]);

        LicensesSetupCategory::create([
            'name' => 'B1',
            'price' => '750000',
            'price_renewal' => '400000'
        ]);

        LicensesSetupCategory::create([
            'name' => 'C1',
            'price' => '800000',
            'price_renewal' => '600000'
        ]);

        LicensesSetupCategory::create([
            'name' => 'C2',
            'price' => '900000',
            'price_renewal' => '800000'
        ]);

    }
}
