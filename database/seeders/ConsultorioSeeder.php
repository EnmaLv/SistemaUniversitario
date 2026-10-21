<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConsultorioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('consultorios')->insert([
            ['nombre' => 'Consultorio 1', 'descripcion' => null, 'sed_id' => 1, 'activo' => 1],
            ['nombre' => 'Consultorio 2', 'descripcion' => null, 'sed_id' => 2, 'activo' => 1],

        ]);
    }
}
