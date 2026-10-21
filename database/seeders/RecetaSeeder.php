<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecetaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('receta')->insert([
            ['nombre_rct' => 'Arepa Con Carne Molida', 'descripcion_rct' => null, 'status_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre_rct' => 'Arepa Con Salchicha', 'descripcion_rct' => null, 'status_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre_rct' => 'Arepa Con Mortadela', 'descripcion_rct' => null, 'status_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre_rct' => 'Arepa Con Mantequilla y Queso', 'descripcion_rct' => null, 'status_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nombre_rct' => 'Arepa Con Pollo', 'descripcion_rct' => null, 'status_id' => 1, 'created_at' => now(), 'updated_at' => now()]
        ]);
    }
}
