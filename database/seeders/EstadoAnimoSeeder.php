<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadoAnimoSeeder extends Seeder
{
    public function run(): void
    {
        $estados = [
            ['valor' => 1, 'nombre' => 'Deprimido'],
            ['valor' => 2, 'nombre' => 'Ansioso'],
            ['valor' => 3, 'nombre' => 'Triste'],
            ['valor' => 4, 'nombre' => 'Frustrado'],
            ['valor' => 5, 'nombre' => 'Molesto / Enojado'],
            ['valor' => 6, 'nombre' => 'Neutral'],
            ['valor' => 7, 'nombre' => 'Tranquilo'],
            ['valor' => 8, 'nombre' => 'Motivado'],
            ['valor' => 9, 'nombre' => 'Feliz'],
            ['valor' => 10, 'nombre' => 'Eufórico / Plenitud'],
        ];

        foreach ($estados as $estado) {
            DB::table('estado_animo')->updateOrInsert(
                ['valor_esa' => $estado['valor'], 'deleted_at' => null],
                ['nombre_esa' => $estado['nombre'], 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
