<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModeloSeeder extends Seeder
{
    public function run(): void
    {
        $modelos = [
            'TOYOTA' => [
                ['nombre' => 'COASTER',  'descripcion' => 'Bus mediano, 25 pasajeros'],
                ['nombre' => 'HIACE',    'descripcion' => 'Camioneta de transporte'],
            ],
            'FORD' => [
                ['nombre' => 'TRANSIT',  'descripcion' => 'Van de transporte de personal'],
                ['nombre' => 'F-350',    'descripcion' => 'Camión de carga pesada'],
            ],
            'CHEVROLET' => [
                ['nombre' => 'NPR',      'descripcion' => 'Camión ligero'],
                ['nombre' => 'EXPRESS',  'descripcion' => 'Van de transporte'],
            ],
            'MERCEDES-BENZ' => [
                ['nombre' => 'SPRINTER', 'descripcion' => 'Van de transporte ejecutivo'],
                ['nombre' => 'OF-1721',  'descripcion' => 'Bus urbano, 40 pasajeros'],
            ],
            'VOLVO' => [
                ['nombre' => '9800',     'descripcion' => 'Bus interprovincial de lujo'],
                ['nombre' => 'B290R',    'descripcion' => 'Bus urbano de piso bajo'],
            ],
            'HYUNDAI' => [
                ['nombre' => 'COUNTY',   'descripcion' => 'Bus mediano, 25 pasajeros'],
                ['nombre' => 'HD-35',    'descripcion' => 'Camión de carga'],
            ],
        ];

        $total = 0;

        foreach ($modelos as $marcaNombre => $items) {
            $marcaId = DB::table('marcas')->where('nombre', $marcaNombre)->value('id');

            if (!$marcaId) {
                $this->command->warn("Marca no encontrada: {$marcaNombre} — se omiten sus modelos.");
                continue;
            }

            foreach ($items as $item) {
                DB::table('modelos')->updateOrInsert(
                    ['marca_id' => $marcaId, 'nombre' => $item['nombre']],
                    [
                        'descripcion' => $item['descripcion'],
                        'estado'      => 1,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]
                );
                $total++;
            }
        }

        $this->command->info('Modelos insertados correctamente: ' . $total);
    }
}