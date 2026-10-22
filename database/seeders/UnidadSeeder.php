<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnidadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('unidad_medida')->insert([
            ['nombre_unm' => 'Unidad', 'abreviatura_unm' => 'U', 'factor_conversion_unm' => 1, 'status_id' => 1],
            ['nombre_unm' => 'Gramo', 'abreviatura_unm' => 'g', 'factor_conversion_unm' => 1, 'status_id' => 1],
            ['nombre_unm' => 'Kilo', 'abreviatura_unm' => 'Kg', 'factor_conversion_unm' => 1000, 'status_id' => 1],
            ['nombre_unm' => 'Mililitro', 'abreviatura_unm' => 'Ml', 'factor_conversion_unm' => 1, 'status_id' => 1],
            ['nombre_unm' => 'Litro', 'abreviatura_unm' => 'Lt', 'factor_conversion_unm' => 1000, 'status_id' => 1],
            ['nombre_unm' => 'Miligramo', 'abreviatura_unm' => 'mg', 'factor_conversion_unm' => 0.001, 'status_id' => 1],
        ]);
    }
}
