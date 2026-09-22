<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MarcaSeeder extends Seeder
{
    public function run(): void
    {
        $marcas = [
            ['nombre' => 'TOYOTA',        'descripcion' => 'Fabricante japonés, buses y camionetas'],
            ['nombre' => 'FORD',          'descripcion' => 'Fabricante estadounidense, vehículos utilitarios'],
            ['nombre' => 'CHEVROLET',     'descripcion' => 'Fabricante estadounidense, buses y camiones'],
            ['nombre' => 'MERCEDES-BENZ', 'descripcion' => 'Fabricante alemán, buses y sprinters'],
            ['nombre' => 'VOLVO',         'descripcion' => 'Fabricante sueco, buses de larga distancia'],
            ['nombre' => 'HYUNDAI',       'descripcion' => 'Fabricante surcoreano, buses y camiones'],
        ];

        foreach ($marcas as $marca) {
            DB::table('marcas')->updateOrInsert(
                ['nombre' => $marca['nombre']],
                [
                    'descripcion' => $marca['descripcion'],
                    'estado'      => 1,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]
            );
        }

        $this->command->info('Marcas insertadas correctamente: ' . count($marcas));
    }
}