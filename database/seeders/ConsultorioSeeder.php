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
        DB::table('consultorio')->insert([
            ['nombre_csr' => 'Consultorio 1', 'descripcion_csr' => null, 'sed_id' => 1, 'status_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre_csr' => 'Consultorio 2', 'descripcion_csr' => null, 'sed_id' => 2, 'status_id' => 1, 'created_at' => now(), 'updated_at' => now()],

        ]);
    }
}
