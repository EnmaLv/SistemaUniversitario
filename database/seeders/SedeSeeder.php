<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SedeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $universidad = DB::table('universidad')->insertGetId([
            'nombre_uni'    => 'Universidad Politécnica Territorial de Portuguesa "Juan de Jesús Montilla"',
            'siglas_uni'    => 'UPTP',
            'sitio_web_uni' => 'https://uptp.edu.ve',
            'created_at'    => now(),
            'updated_at'    => now(),
        ], 'id_uni');

        DB::table('sede')->insert([
            ['nombre_sed' => 'Acarigua', 'direccion_sed' => 'Bellas Artes', 'telefono_sed' => '0424-2222232', 'uni_id' => $universidad, 'status_id' => 1, 'es_principal_sed' => true],
            ['nombre_sed' => 'Guanare', 'direccion_sed' => 'Ciudad de Guanare, Zona Central', 'telefono_sed' => '0424-5556667', 'uni_id' => $universidad, 'status_id' => 1, 'es_principal_sed' => false],
            ['nombre_sed' => 'Santa Rosalia', 'direccion_sed' => 'Avenida Bolivar Entre Calle 11 y 12 Frente a la Plaza Bolivar de El Playon', 'telefono_sed' => '0424-5556668', 'uni_id' => $universidad, 'status_id' => 1, 'es_principal_sed' => false],
            ['nombre_sed' => 'Turen', 'direccion_sed' => 'Colonia Agricola, Turen', 'telefono_sed' => '0424-5556660', 'uni_id' => $universidad, 'status_id' => 1, 'es_principal_sed' => false]
        ]);
    }
}