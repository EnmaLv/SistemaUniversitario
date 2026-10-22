<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnidadDosisSeeder extends Seeder
{
    private const UNIDADES = [
        ['Tableta', 'tab'],
        ['Pastilla', 'past'],
        ['Cápsula', 'cáps'],
        ['Cucharada', 'cda'],
        ['Cucharadita', 'cdta'],
        ['Gota', 'gt'],
        ['Mililitro', 'ml'],
        ['Miligramo', 'mg'],
        ['Ampolla', 'amp'],
        ['Sobre', 'sob'],
        ['Aplicación', 'apl'],
        ['Inhalación', 'inh'],
        ['Supositorio', 'sup'],
        ['Unidad', 'und'],
    ];

    public function run(): void
    {
        foreach (self::UNIDADES as [$nombre, $abreviatura]) {
            if (DB::table('unidad_dosis')->where('nombre_udo', $nombre)->exists()) {
                continue;
            }

            DB::table('unidad_dosis')->insert([
                'nombre_udo'      => $nombre,
                'abreviatura_udo' => $abreviatura,
                'status_id'       => 1,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }
}
