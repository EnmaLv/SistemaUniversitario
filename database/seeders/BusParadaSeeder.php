<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusParadaSeeder extends Seeder
{
    public function run(): void
    {
        $paradas = [
            ['nombre' => 'Llano Lindo',          'lat' => 9.5247450, 'lng' => -69.2386565, 'direccion' => 'Av Circunvalacion'],
            ['nombre' => 'Prado del Sol Morichal', 'lat' => 9.5250942, 'lng' => -69.2364192, 'direccion' => 'Av Circunvalacion'],
            ['nombre' => 'Prados del Sol',       'lat' => 9.5269987, 'lng' => -69.2320579, 'direccion' => 'Av Circunvalacion'],
            ['nombre' => 'Brisas de Sofia',      'lat' => 9.5278293, 'lng' => -69.2302555, 'direccion' => 'Av Circunvalacion'],
            ['nombre' => 'Campo Alegre',         'lat' => 9.5299719, 'lng' => -69.2250139, 'direccion' => 'Av Circunvalacion'],
            ['nombre' => 'Valle Arriba',         'lat' => 9.5330509, 'lng' => -69.2172332, 'direccion' => 'Av Circunvalacion'],
            ['nombre' => 'Irani',                'lat' => 9.5150580, 'lng' => -69.2229009, 'direccion' => 'Av Negro Primero'],
            ['nombre' => 'Gonzalo Barrio',       'lat' => 9.5270675, 'lng' => -69.2128264, 'direccion' => null],
            ['nombre' => 'El Carmelo',           'lat' => 9.5218881, 'lng' => -69.1985671, 'direccion' => null],
            ['nombre' => 'Santa Elena',          'lat' => 9.5243112, 'lng' => -69.1991899, 'direccion' => null],
            ['nombre' => 'Altamira',             'lat' => 9.5342360, 'lng' => -69.2104877, 'direccion' => null],
            ['nombre' => 'La Corteza',           'lat' => 9.5350771, 'lng' => -69.2014590, 'direccion' => null],
            ['nombre' => 'Bosques de Camoruco',  'lat' => 9.5385528, 'lng' => -69.1882843, 'direccion' => 'Av Circunvalacion'],
            ['nombre' => 'Araguaney',            'lat' => 9.5357543, 'lng' => -69.1937453, 'direccion' => null],
            ['nombre' => 'Santa Rita',           'lat' => 9.5427374, 'lng' => -69.1878390, 'direccion' => null],
            ['nombre' => 'UPTP JJ Montilla',     'lat' => 9.5469166, 'lng' => -69.1926348, 'direccion' => null],
        ];

        foreach ($paradas as $parada) {
            DB::table('bus_paradas')->updateOrInsert(
                ['nombre' => $parada['nombre']],
                [
                    'lat'        => $parada['lat'],
                    'lng'        => $parada['lng'],
                    'direccion'  => $parada['direccion'],
                    'estado'     => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('Paradas insertadas correctamente: ' . count($paradas));
    }
}
