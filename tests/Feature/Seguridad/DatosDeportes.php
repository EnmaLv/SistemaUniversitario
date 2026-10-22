<?php

namespace Tests\Feature\Seguridad;

use App\Models\Deporte\DeporteCalendario;
use App\Models\Deporte\DeporteHorario;
use App\Models\Persona;
use App\Support\Deportes\ServicioActividades;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait DatosDeportes
{
    protected function espacio(string $nombre = 'Cancha de prueba'): int
    {
        return DB::table('espacio_deportivo')->insertGetId([
            'nombre_epd' => $nombre . ' ' . random_int(1000, 999999),
            'sed_id'     => $this->sedeA(),
            'status_id'  => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_epd');
    }

    protected function disciplina(string $nombre = 'Disciplina'): int
    {
        return DB::table('actividad_deportiva')->insertGetId([
            'nombre_acd' => $nombre . ' ' . random_int(1000, 999999),
            'tipo_acd'   => 'programada',
            'status_id'  => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_acd');
    }

    protected function persona(): Persona
    {
        do {
            $cedula = (string) random_int(10000000, 99999999);
        } while (DB::table('persona')->where('cedula_per', $cedula)->exists());

        return Persona::create([
            'nombres_per'   => 'Atleta',
            'apellidos_per' => 'Prueba ' . $cedula,
            'cedula_per'    => $cedula,
            'telefono_per'  => '0424-0000000',
            'genero_per'    => 'Otro',
            'correo_per'    => "atleta{$cedula}@prueba.test",
            'id_perfil'     => DB::table('perfil')->orderBy('id_perfil')->value('id_perfil'),
            'sed_id'        => $this->sedeA(),
        ]);
    }

    protected function cedulaInexistente(): string
    {
        do {
            $cedula = (string) random_int(10000000, 99999999);
        } while (DB::table('persona')->where('cedula_per', $cedula)->exists());

        return $cedula;
    }

    protected function bloque(int $disciplina, int $espacio, string $inicio = '08:00', string $fin = '10:00', ?int $cupo = null, ?int $id = null): array
    {
        return ['id' => $id, 'acd_id' => $disciplina, 'epd_id' => $espacio, 'hora_inicio' => $inicio, 'hora_fin' => $fin, 'cupo' => $cupo];
    }

    protected function esporadica($fecha, array $bloques, bool $conInscripcion = true, string $nombre = 'Evento de prueba'): DeporteCalendario
    {
        return ServicioActividades::guardar([
            'nombre_cld'          => $nombre . ' ' . random_int(1000, 999999),
            'tipo_cld'            => DeporteCalendario::TIPO_ESPORADICA,
            'fecha'               => Carbon::parse($fecha)->toDateString(),
            'con_inscripcion_cld' => $conInscripcion,
            'sed_id'              => $this->sedeA(),
        ], $bloques);
    }

    protected function recurrente($desde, $hasta, array $dias, array $bloques, string $nombre = 'Entrenamiento de prueba'): DeporteCalendario
    {
        return ServicioActividades::guardar([
            'nombre_cld'          => $nombre . ' ' . random_int(1000, 999999),
            'tipo_cld'            => DeporteCalendario::TIPO_RECURRENTE,
            'fecha_inicio_cld'    => Carbon::parse($desde)->toDateString(),
            'fecha_fin_cld'       => Carbon::parse($hasta)->toDateString(),
            'dias'                => $dias,
            'con_inscripcion_cld' => true,
            'sed_id'              => $this->sedeA(),
        ], $bloques);
    }

    protected function bloquesDe(DeporteCalendario $actividad)
    {
        return DeporteHorario::query()->with('calendario')->where('cld_id', $actividad->id_cld)->orderBy('id_hde')->get();
    }

    protected function primerBloque(DeporteCalendario $actividad): DeporteHorario
    {
        return $this->bloquesDe($actividad)->firstOrFail();
    }
}
