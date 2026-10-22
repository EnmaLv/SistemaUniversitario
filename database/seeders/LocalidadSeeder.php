<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Laravel\Pail\Files;

class LocalidadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
 {
        // 1. Cargar el JSON
        $path = database_path('data/parroquias.json');
        if(!File::exists($path)) {
            return;
        }
        $json = File::get($path);
        $data = json_decode($json, true);

        // En este caso no usamos unique() porque cada parroquia es un registro distinto
        foreach ($data as $item) {
            // Buscamos el ID del municipio por su nombre
            // Importante: también validamos el estado para evitar conflictos si hay nombres repetidos
            $municipio = DB::table('municipio')
                ->join('estado', 'municipio.esd_id', '=', 'estado.id_esd')
                ->where('municipio.nombre_mun', $item['municipio'])
                ->where('estado.nombre_esd', $item['estado'])
                ->select('municipio.id_mun')
                ->first();

            if ($municipio) {
                // Insertar en la tabla 'localidads' (nombre exacto de tu imagen)
                DB::table('parroquia')->updateOrInsert(
                    [
                        'nombre_par' => $item['parroquia'],
                        'mun_id'     => $municipio->id_mun,
                    ],
                    [
                        'status_id'        => 1,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]
                );
            }
        }
    }
}
