<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoProductoSeeder extends Seeder
{
    public const TIPOS = [
        ['nombre_tpr' => 'Alimento', 'key_mod' => 'comedor'],
        ['nombre_tpr' => 'Medicamento', 'key_mod' => 'salud'],
        ['nombre_tpr' => 'Implemento deportivo', 'key_mod' => 'deporte'],
        ['nombre_tpr' => 'Material de oficina', 'key_mod' => 'administracion'],
    ];

    public function run(): void
    {
        foreach (self::TIPOS as $tipo) {
            $moduloId = DB::table('modulo')->where('key_mod', $tipo['key_mod'])->value('id_mod');

            if (! $moduloId) {
                continue;
            }

            $existente = DB::table('tipo_producto')->where('nombre_tpr', $tipo['nombre_tpr'])->value('id_tpr');

            if ($existente) {
                DB::table('tipo_producto')->where('id_tpr', $existente)->update([
                    'mod_id'     => $moduloId,
                    'updated_at' => now(),
                ]);

                continue;
            }

            DB::table('tipo_producto')->insert([
                'nombre_tpr' => $tipo['nombre_tpr'],
                'mod_id'     => $moduloId,
                'status_id'  => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
