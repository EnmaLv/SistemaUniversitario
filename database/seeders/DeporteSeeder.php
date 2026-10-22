<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeporteSeeder extends Seeder
{
    public function run(): void
    {
        $coordinador = DB::table('user')->where('username_use', 'admin@example.com')->value('id_use')
            ?? DB::table('user')->orderBy('id_use')->value('id_use');

        if (! $coordinador) {
            $this->command?->warn('DeporteSeeder: no hay usuarios; ejecútalo después de PersonaSeeder.');

            return;
        }

        $actividades = [];

        foreach ([
            ['Fútbol sala', 'Práctica y entrenamiento de fútbol sala.', 'programada'],
            ['Voleibol', 'Práctica y entrenamiento de voleibol mixto.', 'programada'],
        ] as [$nombre, $descripcion, $tipo]) {
            $actividades[] = DB::table('actividad_deportiva')->where('nombre_acd', $nombre)->value('id_acd')
                ?? DB::table('actividad_deportiva')->insertGetId([
                    'nombre_acd'      => $nombre,
                    'descripcion_acd' => $descripcion,
                    'tipo_acd'        => $tipo,
                    'status_id'       => 1,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ], 'id_acd');
        }

        $inicio = Carbon::today()->startOfMonth();
        $fin = Carbon::today()->addMonths(6)->endOfMonth();
        $semana = Carbon::today()->startOfWeek(Carbon::MONDAY)->toDateString();

        $sedes = DB::table('sede')->where('status_id', 1)->whereNull('deleted_at')->orderBy('id_sed')->get(['id_sed', 'nombre_sed']);

        foreach ($sedes as $sede) {
            $espacios = [];

            foreach ([
                ['Cancha múltiple', 'Cancha techada de usos múltiples.', 'Área deportiva principal'],
                ['Cancha de voleibol', 'Cancha de arena para voleibol.', 'Detrás del edificio administrativo'],
            ] as [$nombre, $descripcion, $ubicacion]) {
                $espacios[] = DB::table('espacio_deportivo')->where('sed_id', $sede->id_sed)->where('nombre_epd', $nombre)->value('id_epd')
                    ?? DB::table('espacio_deportivo')->insertGetId([
                        'nombre_epd'      => $nombre,
                        'descripcion_epd' => $descripcion,
                        'ubicacion_epd'   => $ubicacion,
                        'sed_id'          => $sede->id_sed,
                        'status_id'       => 1,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ], 'id_epd');
            }

            $nombreCalendario = 'Período deportivo ' . $inicio->year . ' - ' . $sede->nombre_sed;

            if (DB::table('calendario_deportivo')->where('nombre_cld', $nombreCalendario)->exists()) {
                continue;
            }

            $calendario = DB::table('calendario_deportivo')->insertGetId([
                'nombre_cld'       => $nombreCalendario,
                'descripcion_cld'  => 'Calendario de actividades deportivas de la sede ' . $sede->nombre_sed . '.',
                'fecha_inicio_cld' => $inicio->toDateString(),
                'fecha_fin_cld'    => $fin->toDateString(),
                'sed_id'           => $sede->id_sed,
                'use_id'           => $coordinador,
                'status_id'        => 1,
                'created_at'       => now(),
                'updated_at'       => now(),
            ], 'id_cld');

            foreach ([
                [$actividades[0], $espacios[0], 'lunes', '15:00:00', '15:45:00', 20],
                [$actividades[0], $espacios[0], 'miercoles', '15:00:00', '15:45:00', 20],
                [$actividades[1], $espacios[1], 'martes', '16:30:00', '17:15:00', 12],
                [$actividades[1], $espacios[1], 'jueves', '16:30:00', '17:15:00', 12],
            ] as [$actividad, $espacio, $dia, $horaInicio, $horaFin, $cupo]) {
                DB::table('horario_deportivo')->insert([
                    'dia_semana_hde'    => $dia,
                    'semana_inicio_hde' => $semana,
                    'hora_inicio_hde'   => $horaInicio,
                    'hora_fin_hde'      => $horaFin,
                    'cupo_maximo_hde'   => $cupo,
                    'estado_hde'        => 'activo',
                    'cld_id'            => $calendario,
                    'acd_id'            => $actividad,
                    'epd_id'            => $espacio,
                    'use_id'            => $coordinador,
                    'status_id'         => 1,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }
        }
    }
}
