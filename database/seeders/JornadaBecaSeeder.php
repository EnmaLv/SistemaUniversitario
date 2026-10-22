<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Becas\Beneficio;
use App\Models\Becas\Lapso;
use App\Models\Becas\JornadaBeca;
use Carbon\Carbon;

class JornadaBecaSeeder extends Seeder
{
    public function run(): void
    {
        $beneficios = [
            [
                'nombre_beneficio_bes' => 'Beca Comedor',
                'descripcion_bes' => 'Subsidio completo para el servicio de comedor universitario diario.',
                'slug_bes' => 'beca-comedor',
                'cupones_disponibles_bes' => 300,
                'cupones_ocupados_bes' => 0,
                'status_id' => Beneficio::STATUS_ACTIVO,
            ],
            [
                'nombre_beneficio_bes' => 'Beca Transporte',
                'descripcion_bes' => 'Subsidio para el transporte universitario en las rutas establecidas.',
                'slug_bes' => 'beca-transporte',
                'cupones_disponibles_bes' => 200,
                'cupones_ocupados_bes' => 0,
                'status_id' => Beneficio::STATUS_ACTIVO,
            ],
            [
                'nombre_beneficio_bes' => 'Beca de Ayuda Económica',
                'descripcion_bes' => 'Apoyo monetario mensual para estudiantes con vulnerabilidad socioeconómica.',
                'slug_bes' => 'beca-ayuda-economica',
                'cupones_disponibles_bes' => 100,
                'cupones_ocupados_bes' => 0,
                'status_id' => Beneficio::STATUS_ACTIVO,
            ],
        ];

        foreach ($beneficios as $b) {
            Beneficio::firstOrCreate(['slug_bes' => $b['slug_bes']], $b);
        }

        $lapsos = [
            [
                'codigo_laa' => '2026-I',
                'fecha_inicio_laa' => Carbon::create(2026, 1, 15),
                'fecha_fin_laa' => Carbon::create(2026, 7, 15),
                'es_actual_laa' => true,
                'permite_solicitudes_laa' => true,
            ],
            [
                'codigo_laa' => '2026-II',
                'fecha_inicio_laa' => Carbon::create(2026, 9, 15),
                'fecha_fin_laa' => Carbon::create(2027, 2, 15),
                'es_actual_laa' => false,
                'permite_solicitudes_laa' => false,
            ],
        ];

        foreach ($lapsos as $l) {
            Lapso::firstOrCreate(['codigo_laa' => $l['codigo_laa']], $l);
        }

        $comedor = Beneficio::where('slug_bes', '=', 'beca-comedor', 'and')->first();
        $transporte = Beneficio::where('slug_bes', '=', 'beca-transporte', 'and')->first();
        $ayuda = Beneficio::where('slug_bes', '=', 'beca-ayuda-economica', 'and')->first();

        $lapsoActual = Lapso::where('codigo_laa', '=', '2026-I', 'and')->first();
        $lapsoSiguiente = Lapso::where('codigo_laa', '=', '2026-II', 'and')->first();

        $jornadas = [
            [
                'nombre_jornada_jnb' => 'Convocatoria Beca Comedor 2026-I',
                'descripcion_jornada_jnb' => 'Proceso de postulación y renovación para la beca de comedor durante el lapso académico 2026-I.',
                'bes_id' => $comedor->id_bes,
                'laa_id' => $lapsoActual->id_laa,
                'fecha_inicio_solicitud_jnb' => Carbon::now()->subDays(5),
                'fecha_fin_solicitud_jnb' => Carbon::now()->addDays(15),
                'cupos_maximos_jnb' => 150,
                'cupos_asignados_jnb' => 0,
                'status_id' => JornadaBeca::STATUS_ACTIVO,
            ],
            [
                'nombre_jornada_jnb' => 'Convocatoria Beca Transporte 2026-I',
                'descripcion_jornada_jnb' => 'Proceso de postulación para el beneficio de transporte en las rutas universitarias.',
                'bes_id' => $transporte->id_bes,
                'laa_id' => $lapsoActual->id_laa,
                'fecha_inicio_solicitud_jnb' => Carbon::now()->subDays(5),
                'fecha_fin_solicitud_jnb' => Carbon::now()->addDays(10),
                'cupos_maximos_jnb' => 100,
                'cupos_asignados_jnb' => 0,
                'status_id' => JornadaBeca::STATUS_ACTIVO,
            ],
            [
                'nombre_jornada_jnb' => 'Convocatoria Ayuda Económica Especial 2026-I',
                'descripcion_jornada_jnb' => 'Asignación de ayudas económicas para estudiantes en situaciones excepcionales.',
                'bes_id' => $ayuda->id_bes,
                'laa_id' => $lapsoActual->id_laa,
                'fecha_inicio_solicitud_jnb' => Carbon::now()->subDays(10),
                'fecha_fin_solicitud_jnb' => Carbon::now()->subDay(),
                'cupos_maximos_jnb' => 50,
                'cupos_asignados_jnb' => 0,
                'status_id' => JornadaBeca::STATUS_INACTIVO,
            ],
        ];

        foreach ($jornadas as $j) {
            JornadaBeca::firstOrCreate(
                ['nombre_jornada_jnb' => $j['nombre_jornada_jnb']],
                $j
            );
        }
    }
}
