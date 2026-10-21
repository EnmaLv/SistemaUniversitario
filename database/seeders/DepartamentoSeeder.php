<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartamentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('departamento')->insert([
            ['nombre_dep' => 'Bienestar Estudiantil', 'descripcion_dep' => 'Departamento encargado del bienestar de los estudiantes', 'status_id' => 1]
        ]);
    }
}
