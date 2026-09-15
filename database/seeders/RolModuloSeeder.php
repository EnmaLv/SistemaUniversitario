<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolModuloSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('rol_modulo')->insert([
            ['rol_id' => 3, 'modulo_id' => 2],
            ['rol_id' => 4, 'modulo_id' => 1],
            ['rol_id' => 5, 'modulo_id' => 1],
            ['rol_id' => 6, 'modulo_id' => 4],
            ['rol_id' => 7, 'modulo_id' => 6],
            ['rol_id' => 8, 'modulo_id' => 1],
        ]);
    }
}
