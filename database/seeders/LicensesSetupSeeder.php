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
        // Categorías normales
        LicensesSetupCategory::create([
            'name' => 'A2',
            'type' => 'normal',
            'price_exam' => 100000,
            'price_slide' => 150000,
            'school_letter' =>100000,
            'price_fees' => 200000,
            'price_no_course' => 450000,
            'is_active' => true
        ]);

        LicensesSetupCategory::create([
            'name' => 'B1',
            'type' => 'normal',
            'price_exam' => 150000,
            'price_slide' => 200000,
            'school_letter' =>100000,
            'price_fees' => 250000,
            'price_no_course' => 750000,
            'is_active' => true
        ]);

        // Categorías de renovación
        LicensesSetupCategory::create([
            'name' => 'Renovación A2',
            'type' => 'renovation',
            'price_renewal_exam_client' => 80000,
            'price_renewal_exam_slide_client' => 120000,
            'price_renewal_exam_processor' => 70000,
            'price_renewal_exam_slide_processor' => 110000,
            'is_active' => true
        ]);

        LicensesSetupCategory::create([
            'name' => 'Renovación B1',
            'type' => 'renovation',
            'price_renewal_exam_client' => 120000,
            'price_renewal_exam_slide_client' => 180000,
            'price_renewal_exam_processor' => 110000,
            'price_renewal_exam_slide_processor' => 170000,
            'is_active' => true
        ]);
    }
}
