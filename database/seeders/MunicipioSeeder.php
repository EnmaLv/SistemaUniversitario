<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class MunicipioSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/parroquias.json');
        $data = json_decode(File::get($path), true);

        // Agrupamos por municipio para procesar cada uno una sola vez
        $municipiosUnicos = collect($data)->unique(function ($item) {
            return $item['estado'].$item['municipio'];
        });

        foreach ($municipiosUnicos as $item) {
            $nombreEstado = trim($item['estado']);
            $nombreMunicipio = trim($item['municipio']);

            // Buscamos el estado ignorando mayúsculas/minúsculas
            $estado = DB::table('estado')
                ->whereRaw('LOWER(nombre_esd) = ?', [mb_strtolower($nombreEstado)])
                ->first();

            if ($estado) {
                DB::table('municipio')->updateOrInsert(
                    [
                        'nombre_mun' => $nombreMunicipio,
                        'esd_id'        => $estado->id_esd,
                    ],
                    [
                        'status_id'        => 1,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]
                );
            } else {
                // Esto te avisará en la terminal si un estado no se encuentra
                $this->command->warn("Estado no encontrado: {$nombreEstado} para el municipio {$nombreMunicipio}");
            }
        }
        
        $this->command->info("Proceso de municipios finalizado.");
    }
}
