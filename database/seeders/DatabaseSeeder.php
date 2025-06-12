<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            CategoryRevocationSeeder::class,
            LawyerSeeder::class,
            FilterSeeder::class,
            ProcessCategorySeeder::class,
            LicensesSetupSeeder::class,
            SchoolSetupSeeder::class,
            ControversyCategorySeeder::class,
            RevocationCategorySeeder::class,
            CourseCategorySeeder::class,
        ]);
    }
}
