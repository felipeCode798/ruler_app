<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CourseCategory;

class CourseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CourseCategory::create([
            'code' => 'B',
            'name' => 'Curso Básico',
            'transit_value_50' => 220000,
            'processor_value_50' => 235000,
            'client_value_50' => 270000,
            'transit_value_25' => 285000,
            'processor_value_25' => 300000,
            'client_value_25' => 330000,
            'is_active' => true
        ]);

        CourseCategory::create([
            'code' => 'C',
            'name' => 'Curso Intermedio',
            'transit_value_50' => 350000,
            'processor_value_50' => 365000,
            'client_value_50' => 400000,
            'transit_value_25' => 500000,
            'processor_value_25' => 515000,
            'client_value_25' => 550000,
            'is_active' => true
        ]);
    }
}
