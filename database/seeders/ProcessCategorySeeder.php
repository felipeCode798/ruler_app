<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProcessCategory;

class ProcessCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProcessCategory::create([
            'name' => 'Cobro Coactivo',
            'value_process' => 28,
        ]);

        ProcessCategory::create([
            'name' => 'Adeudo',
            'value_process' => 30,
        ]);

        ProcessCategory::create([
            'name' => 'Sin Resolucion',
            'value_process' => 30,
        ]);

        ProcessCategory::create([
            'name' => 'Acuedo de Pago',
            'value_process' => 35,
        ]);

        ProcessCategory::create([
            'name' => 'Prescripcion',
            'value_process' => 45,
        ]);

        ProcessCategory::create([
            'name' => 'Comparendo',
            'value_process' => 50,
        ]);

    }
}
