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
                'consultorio_id' => 1,
                'id_rol_usuario' => 4,
                'dias'           => ['lunes', 'martes', 'miercoles'],
                'jornada'        => 'Matutino',
            ],
            [
                'consultorio_id' => 1,
                'id_rol_usuario' => 6,
                'dias'           => ['lunes', 'martes', 'miercoles'],
                'jornada'        => 'Matutino',
            ],

            //Rango especifico de horas Dc. Genesis y enfermera Carmen
            [
                'consultorio_id' => 1,
                'id_rol_usuario' => 5,
                'dias'           => ['martes', 'miercoles', 'jueves'],
                'desde'          => '15:05',
                'hasta'          => '20:20',
            ],
            [
                'consultorio_id' => 1,
                'id_rol_usuario' => 7,
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
                            'consultorio_id' => $asig['consultorio_id'],
                            'id_rol_usuario' => $asig['id_rol_usuario'],
                            'dia'            => strtolower($dia),
                            'hora_inicio'    => $bloque['inicio'],
                            'hora_fin'       => $bloque['fin'],
                            'descripcion'    => null,
                            'activo'         => 1,
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ];
                    }
                }
            }
        }

        // 3. Inserción masiva en una sola consulta
        DB::table('horario_consultorios')->insert($horarios);
    }
}