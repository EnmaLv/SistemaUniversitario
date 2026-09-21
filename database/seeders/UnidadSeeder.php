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
        DB::table('unidades')->insert([
            ['nombre' => 'Unidad', 'abreviatura' => 'U', 'factor_a_gramo' => 1],
            ['nombre' => 'Gramo', 'abreviatura' => 'g', 'factor_a_gramo' => 1],
            ['nombre' => 'Kilo', 'abreviatura' => 'Kg', 'factor_a_gramo' => 1000],
            ['nombre' => 'Mililitro', 'abreviatura' => 'Ml', 'factor_a_gramo' => 1],
            ['nombre' => 'Litro', 'abreviatura' => 'Lt', 'factor_a_gramo' => 1000]
        ]);
    }
}
