<?php

namespace Database\Seeders;

use App\Models\salud\HorarioConsultorio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HorarioConsultoriosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $horarios = [];

        $asignaciones = [
            //Por jornada Matutina, Dc. Argenis y enfermera Denis
            [
                'csr_id'         => 1,
                'usr_id'         => 4,
                'dias'           => ['lunes', 'martes', 'miercoles'],
                'jornada'        => 'Matutino',
            ],
            [
                'csr_id'         => 1,
                'usr_id'         => 6,
                'dias'           => ['lunes', 'martes', 'miercoles'],
                'jornada'        => 'Matutino',
            ],

            //Rango especifico de horas Dc. Genesis y enfermera Carmen
            [
                'csr_id'         => 1,
                'usr_id'         => 5,
                'dias'           => ['martes', 'miercoles', 'jueves'],
                'desde'          => '15:05',
                'hasta'          => '20:20',
            ],
            [
                'csr_id'         => 1,
                'usr_id'         => 7,
                'dias'           => ['martes', 'miercoles', 'jueves'],
                'desde'          => '15:05',
                'hasta'          => '20:20',
            ],
        ];

        // Procesamiento automático de los bloques
        foreach ($asignaciones as $asig) {
            $dias = (array) $asig['dias'];

            foreach (HorarioConsultorio::BLOQUES as $nombreJornada => $bloques) {
                // Si se especificó una jornada particular y no coincide, la saltamos
                if (isset($asig['jornada']) && $asig['jornada'] !== $nombreJornada) {
                    continue;
                }

                foreach ($bloques as $bloque) {
                    // Filtrar si se definió un rango de hora 'desde' o 'hasta'
                    if (isset($asig['desde']) && $bloque['inicio'] < $asig['desde']) {
                        continue;
                    }
                    if (isset($asig['hasta']) && $bloque['fin'] > $asig['hasta']) {
                        continue;
                    }

                    // Generar un registro por cada día configurado
                    foreach ($dias as $dia) {
                        $horarios[] = [
                            'csr_id'          => $asig['csr_id'],
                            'usr_id'          => $asig['usr_id'],
                            'dia_hrc'         => strtolower($dia),
                            'hora_inicio_hrc' => $bloque['inicio'],
                            'hora_fin_hrc'    => $bloque['fin'],
                            'descripcion_hrc' => null,
                            'status_id'       => 1,
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ];
                    }
                }
            }
        }

        // 3. Inserción masiva en una sola consulta
        DB::table('horario_consultorio')->insert($horarios);
    }
}