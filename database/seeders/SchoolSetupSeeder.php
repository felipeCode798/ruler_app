<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SchoolSetup;

class SchoolSetupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SchoolSetup::create([
            'name_school' => 'Blasscar',
            'address' => 'calle 12 # 22a 78',
            'phone' => '3153692739',
            'responsible' => 'Juliana',
            'total_pins' => '3',
        ]);

        SchoolSetup::create([
            'name_school' => 'Nascar',
            'address' => 'calle 12 # 22a 78',
            'phone' => '3333333',
            'responsible' => 'Juliana',
            'total_pins' => '3',
        ]);
    }
}
