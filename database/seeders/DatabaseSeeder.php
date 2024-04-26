<?php

namespace Database\Seeders;


use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        DB::table('users')->insert([
            'name' => 'Felipe',
            'email' => 'felipe@gmail.com',
            'password' => Hash::make('123456789'),
            'phone' => 3153692739,
            'dni'=> 1144100609,
        ]);
        DB::table('users')->insert([
            'name' => 'Camila',
            'email' => 'camila@gmail.com',
            'password' => Hash::make('123456789'),
            'phone' => 3117127060,
            'dni'=> 1111111111,
        ]);
        DB::table('roles')->insert([
            'name' => 'Tramitador',
            'guard_name' => 'web',
        ]);
        DB::table('roles')->insert([
            'name' => 'Cliente',
            'guard_name' => 'web',
        ]);
        DB::table('permissions')->insert([
            'name' => 'Editar Licencia',
            'guard_name' => 'web',
        ]);
        DB::table('model_has_roles')->insert([
            'role_id' => 1,
            'model_type' => 'App\Models\User',
            'model_id' => 1,
        ]);
        DB::table('model_has_roles')->insert([
            'role_id' => 2,
            'model_type' => 'App\Models\User',
            'model_id' => 2,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id' => 1,
            'role_id' => 1,
        ]);
        DB::table('role_has_permissions')->insert([
            'permission_id' => 1,
            'role_id' => 2,
        ]);


    }
}
