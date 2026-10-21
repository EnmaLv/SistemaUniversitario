<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModulosSeeder extends Seeder
{
    public function run()
    {
        $modulos = [
            ['key_mod' => 'salud', 'nombre_mod' => 'Salud', 'descripcion_mod' => 'Módulo de atención médica'],
            ['key_mod' => 'comedor', 'nombre_mod' => 'Comedor', 'descripcion_mod' => 'Módulo de comedor'],
            ['key_mod' => 'administracion', 'nombre_mod' => 'Administración', 'descripcion_mod' => 'Configuración y usuarios'],
            ['key_mod' => 'beca', 'nombre_mod' => 'Beca', 'descripcion_mod' => 'Módulo de beca'],
            ['key_mod' => 'transporte', 'nombre_mod' => 'Transporte', 'descripcion_mod' => 'Módulo de transporte universitario'],
            ['key_mod' => 'psicologia', 'nombre_mod' => 'Psicología', 'descripcion_mod' => 'Módulo de psicología'],
        ];

        foreach ($modulos as $m) {
            DB::table('modulo')->updateOrInsert(['key_mod' => $m['key_mod']], $m);
        }
    }
}