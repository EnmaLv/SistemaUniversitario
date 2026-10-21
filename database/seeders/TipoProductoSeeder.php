<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tipo_producto')->insert([
            ['nombre_tpr' => 'Alimento', 'mod_id' => 2, 'status_id' => 1],
            ['nombre_tpr' => 'Medicamento', 'mod_id' => 1, 'status_id' => 1]
        ]);
    }
}
