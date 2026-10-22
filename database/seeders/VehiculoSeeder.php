<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehiculoSeeder extends Seeder
{
    public function run(): void
    {
        $sedeId = DB::table('sede')->orderBy('id_sed')->value('id_sed');

        if (!$sedeId) {
            $this->command->error('No hay sedes registradas. Ejecuta SedeSeeder antes.');
            return;
        }

        $vehiculos = [
            [
                'placa'                     => 'AB123CD',
                'marca'                     => 'TOYOTA',
                'modelo'                    => 'COASTER',
                'anio'                      => 2020,
                'color'                     => 'BLANCO',
                'peso'                      => 3500.00,
                'cantidad_pasajeros'        => 25,
                'tipo_combustible'          => 'DIESEL',
                'cantidad_cilindros'        => 4,
                'capacidad_tanque_litros'   => 95.00,
                'nivel_combustible_actual'  => 80.00,
                'consumo_urbano'            => 8.500,
                'consumo_carretera'         => 6.200,
                'consumo_relenti'           => 2.100,
                'km_actual'                 => 45000.00,
                'km_proximo_mantenimiento'  => 50000.00,
                'estado'                    => 'disponible',
                'activo'                    => 1,
            ],
            [
                'placa'                     => 'EF456GH',
                'marca'                     => 'MERCEDES-BENZ',
                'modelo'                    => 'OF-1721',
                'anio'                      => 2019,
                'color'                     => 'AZUL',
                'peso'                      => 12000.00,
                'cantidad_pasajeros'        => 40,
                'tipo_combustible'          => 'DIESEL',
                'cantidad_cilindros'        => 6,
                'capacidad_tanque_litros'   => 200.00,
                'nivel_combustible_actual'  => 150.00,
                'consumo_urbano'            => 12.000,
                'consumo_carretera'         => 9.500,
                'consumo_relenti'           => 3.000,
                'km_actual'                 => 120000.00,
                'km_proximo_mantenimiento'  => 125000.00,
                'estado'                    => 'disponible',
                'activo'                    => 1,
            ],
            [
                'placa'                     => 'IJ789KL',
                'marca'                     => 'HYUNDAI',
                'modelo'                    => 'COUNTY',
                'anio'                      => 2022,
                'color'                     => 'GRIS',
                'peso'                      => 4000.00,
                'cantidad_pasajeros'        => 28,
                'tipo_combustible'          => 'GASOLINA',
                'cantidad_cilindros'        => 4,
                'capacidad_tanque_litros'   => 100.00,
                'nivel_combustible_actual'  => 60.00,
                'consumo_urbano'            => 7.800,
                'consumo_carretera'         => 5.900,
                'consumo_relenti'           => 1.800,
                'km_actual'                 => 18000.00,
                'km_proximo_mantenimiento'  => 25000.00,
                'estado'                    => 'disponible',
                'activo'                    => 1,
            ],
        ];

        $insertados = 0;

        foreach ($vehiculos as $v) {
            $modeloId = DB::table('modelo_vehiculo')
                ->join('marca_vehiculo', 'marca_vehiculo.id_mav', '=', 'modelo_vehiculo.mav_id')
                ->where('marca_vehiculo.nombre_mav', $v['marca'])
                ->where('modelo_vehiculo.nombre_mov', $v['modelo'])
                ->value('modelo_vehiculo.id_mov');

            $combustibleId = DB::table('tipo_combustible')
                ->where('nombre_tcb', $v['tipo_combustible'])
                ->value('id_tcb');

            if (!$modeloId || !$combustibleId) {
                $this->command->warn("Vehículo omitido (placa {$v['placa']}): modelo o combustible no encontrado.");
                continue;
            }

            DB::table('vehiculo_transporte')->updateOrInsert(
                ['placa_vtr' => $v['placa']],
                [
                    'mov_id'                       => $modeloId,
                    'anio_vtr'                     => $v['anio'],
                    'color_vtr'                    => $v['color'],
                    'peso_vtr'                     => $v['peso'],
                    'capacidad_pasajeros_vtr'      => $v['cantidad_pasajeros'],
                    'tcb_id'                       => $combustibleId,
                    'cantidad_cilindros_vtr'       => $v['cantidad_cilindros'],
                    'capacidad_tanque_litros_vtr'  => $v['capacidad_tanque_litros'],
                    'nivel_combustible_actual_vtr' => $v['nivel_combustible_actual'],
                    'consumo_urbano_vtr'           => $v['consumo_urbano'],
                    'consumo_carretera_vtr'        => $v['consumo_carretera'],
                    'consumo_ralenti_vtr'          => $v['consumo_relenti'],
                    'km_actual_vtr'                => $v['km_actual'],
                    'km_proximo_mantenimiento_vtr' => $v['km_proximo_mantenimiento'],
                    'sed_id'                       => $sedeId,
                    'status_id'                    => $v['activo'] ? 1 : 2,
                    'estado_operativo_vtr'         => $v['estado'],
                    'created_at'                   => now(),
                    'updated_at'                   => now(),
                ]
            );

            $insertados++;
        }

        $this->command->info('Vehículos insertados correctamente: ' . $insertados);
    }
}