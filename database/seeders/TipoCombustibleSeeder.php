<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoCombustibleSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['nombre' => 'GASOLINA', 'descripcion' => 'Combustible derivado del petróleo, 91-95 octanos'],
            ['nombre' => 'DIESEL',   'descripcion' => 'Combustible diésel para vehículos pesados'],
            ['nombre' => 'GNV',      'descripcion' => 'Gas Natural Vehicular comprimido'],
        ];

        foreach ($tipos as $tipo) {
            DB::table('tipo_combustible')->updateOrInsert(
                ['nombre_tcb' => $tipo['nombre']],
                [
                    'descripcion_tcb' => $tipo['descripcion'],
                    'status_id'       => 1,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]
            );
        }

        $this->command->info('Tipos de combustible insertados correctamente: ' . count($tipos));
    }
}