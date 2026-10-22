<?php

namespace Tests\Feature;

use App\Http\Controllers\Deporte\HorarioDeporteController;
use App\Models\Deporte\DeporteHorario;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DeporteHorarioGlobalOccupancyTest extends TestCase
{
    use RefreshDatabase;

    private function seedHorarioContext(): array
    {
        $semana = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        $sedeId = DB::table('sede')->insertGetId([
            'nombre' => 'Sede prueba',
            'direccion' => 'Dirección prueba',
            'telefono' => '04141234567',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $perfilId = DB::table('perfil')->insertGetId([
            'nombre_perfil' => 'Administrador',
            'estado' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $personaId = DB::table('persona')->insertGetId([
            'nombre_persona' => 'Usuario',
            'apellido_persona' => 'Prueba',
            'cedula_persona' => '12345678',
            'telefono_persona' => '04141234567',
            'genero_persona' => 'M',
            'edad_persona' => 30,
            'fecha_nacimiento_persona' => '1995-01-01',
            'email_persona' => 'usuario@prueba.com',
            'semestre_persona' => null,
            'estado' => true,
            'id_perfil' => $perfilId,
            'id_sede' => $sedeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $usuarioId = DB::table('usuario')->insertGetId([
            'id_persona' => $personaId,
            'id_perfil' => $perfilId,
            'username' => 'deportetest',
            'password' => bcrypt('secret123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $calendarioId = DB::table('deporte_calendario')->insertGetId([
            'nombre' => 'Calendario prueba',
            'descripcion' => 'Prueba',
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-12-31',
            'id_sede' => $sedeId,
            'activo' => true,
            'creado_por' => $usuarioId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $actividadId = DB::table('deporte_actividad')->insertGetId([
            'nombre' => 'Fútbol',
            'descripcion' => 'Actividad de prueba',
            'tipo' => 'programada',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $espacioAId = DB::table('deporte_espacio')->insertGetId([
            'nombre' => 'Cancha Principal',
            'descripcion' => 'Espacio A',
            'ubicacion' => 'Sede principal',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $espacioBId = DB::table('deporte_espacio')->insertGetId([
            'nombre' => 'Cancha de Voleibol',
            'descripcion' => 'Espacio B',
            'ubicacion' => 'Sede principal',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return compact(
            'calendarioId',
            'actividadId',
            'espacioAId',
            'espacioBId',
            'usuarioId',
            'semana'
        );
    }

    public function test_same_day_interval_is_blocked_globally_even_if_space_changes(): void
    {
        $ctx = $this->seedHorarioContext();
        $semana = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        DB::table('deporte_horario')->insert([
            'id_calendario' => $ctx['calendarioId'],
            'id_actividad' => $ctx['actividadId'],
            'id_espacio' => $ctx['espacioAId'],
            'dia_semana' => 'lunes',
            'fecha_especifica' => null,
            'hora_inicio' => '08:00:00',
            'hora_fin' => '09:00:00',
            'cupo_maximo' => 10,
            'observaciones' => null,
            'estado' => 'activo',
            'creado_por' => $ctx['usuarioId'],
            'semana_inicio' => $semana,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertTrue(DeporteHorario::overlaps(
            $ctx['espacioBId'],
            'lunes',
            '08:30:00',
            '09:30:00',
            null,
            null,
            $semana
        ));
    }

    public function test_same_time_on_different_day_is_not_conflict(): void
    {
        $ctx = $this->seedHorarioContext();
        $semana = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        DB::table('deporte_horario')->insert([
            'id_calendario' => $ctx['calendarioId'],
            'id_actividad' => $ctx['actividadId'],
            'id_espacio' => $ctx['espacioAId'],
            'dia_semana' => 'lunes',
            'fecha_especifica' => null,
            'hora_inicio' => '08:00:00',
            'hora_fin' => '09:00:00',
            'cupo_maximo' => 10,
            'observaciones' => null,
            'estado' => 'activo',
            'creado_por' => $ctx['usuarioId'],
            'semana_inicio' => $semana,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertFalse(DeporteHorario::overlaps(
            $ctx['espacioBId'],
            'martes',
            '08:30:00',
            '09:30:00',
            null,
            null,
            $semana
        ));
    }

    public function test_editing_allows_current_record_to_be_excluded_from_conflict_check(): void
    {
        $ctx = $this->seedHorarioContext();
        $semana = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        $recordId = DB::table('deporte_horario')->insertGetId([
            'id_calendario' => $ctx['calendarioId'],
            'id_actividad' => $ctx['actividadId'],
            'id_espacio' => $ctx['espacioAId'],
            'dia_semana' => 'lunes',
            'fecha_especifica' => null,
            'hora_inicio' => '08:00:00',
            'hora_fin' => '09:00:00',
            'cupo_maximo' => 10,
            'observaciones' => null,
            'estado' => 'activo',
            'creado_por' => $ctx['usuarioId'],
            'semana_inicio' => $semana,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertFalse(DeporteHorario::overlaps(
            $ctx['espacioAId'],
            'lunes',
            '08:15:00',
            '08:45:00',
            $recordId,
            null,
            $semana
        ));
    }

    public function test_conflicting_block_is_rejected_by_server_when_saving(): void
    {
        $ctx = $this->seedHorarioContext();
        $semana = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        DB::table('deporte_horario')->insert([
            'id_calendario' => $ctx['calendarioId'],
            'id_actividad' => $ctx['actividadId'],
            'id_espacio' => $ctx['espacioAId'],
            'dia_semana' => 'lunes',
            'fecha_especifica' => null,
            'hora_inicio' => '08:00:00',
            'hora_fin' => '09:00:00',
            'cupo_maximo' => 10,
            'observaciones' => null,
            'estado' => 'activo',
            'creado_por' => $ctx['usuarioId'],
            'semana_inicio' => $semana,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs(\App\Models\Usuario::findOrFail($ctx['usuarioId']));

        $request = Request::create(
            route('admin.deportes.maestros.horarios.store'),
            'POST',
            [
                'tipo_horario' => 'recurrente',
                'id_calendario' => $ctx['calendarioId'],
                'id_actividad' => $ctx['actividadId'],
                'id_espacio' => $ctx['espacioBId'],
                'semana_inicio' => $semana,
                'bloques' => [
                    'lunes|08:30|09:30',
                ],
                'cupo_maximo' => 10,
                'observaciones' => 'Prueba conflicto server',
            ]
        );
        $request->setLaravelSession($this->app['session.store']);
        $request->headers->set('referer', route('admin.deportes.maestros.horarios.create'));

        $response = app(HorarioDeporteController::class)->store($request);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertTrue(session('errors')?->has('bloques'));
        $this->assertSame(1, DB::table('deporte_horario')->count());
    }

    public function test_recurrent_block_conflicts_with_specific_date_on_same_weekday(): void
    {
        $ctx = $this->seedHorarioContext();
        $semana = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $specificDate = Carbon::parse($semana)->addDays(0)->format('Y-m-d');

        DB::table('deporte_horario')->insert([
            'id_calendario' => $ctx['calendarioId'],
            'id_actividad' => $ctx['actividadId'],
            'id_espacio' => $ctx['espacioAId'],
            'dia_semana' => 'lunes',
            'fecha_especifica' => null,
            'hora_inicio' => '08:00:00',
            'hora_fin' => '09:00:00',
            'cupo_maximo' => 10,
            'observaciones' => null,
            'estado' => 'activo',
            'creado_por' => $ctx['usuarioId'],
            'semana_inicio' => $semana,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertTrue(DeporteHorario::overlaps(
            $ctx['espacioBId'],
            null,
            '08:30:00',
            '09:30:00',
            null,
            $specificDate,
            $semana
        ));
    }

    public function test_recurrent_block_does_not_conflict_with_different_weekday(): void
    {
        $ctx = $this->seedHorarioContext();
        $semana = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $specificDate = Carbon::parse($semana)->addDays(1)->format('Y-m-d');

        DB::table('deporte_horario')->insert([
            'id_calendario' => $ctx['calendarioId'],
            'id_actividad' => $ctx['actividadId'],
            'id_espacio' => $ctx['espacioAId'],
            'dia_semana' => 'lunes',
            'fecha_especifica' => null,
            'hora_inicio' => '08:00:00',
            'hora_fin' => '09:00:00',
            'cupo_maximo' => 10,
            'observaciones' => null,
            'estado' => 'activo',
            'creado_por' => $ctx['usuarioId'],
            'semana_inicio' => $semana,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertFalse(DeporteHorario::overlaps(
            $ctx['espacioBId'],
            null,
            '08:30:00',
            '09:30:00',
            null,
            $specificDate,
            $semana
        ));
    }

    public function test_specific_date_conflict_ignores_space_activity_calendar_change(): void
    {
        $ctx = $this->seedHorarioContext();
        $specificDate = Carbon::now()->startOfWeek(Carbon::MONDAY)->addDays(0)->format('Y-m-d');

        DB::table('deporte_horario')->insert([
            'id_calendario' => $ctx['calendarioId'],
            'id_actividad' => $ctx['actividadId'],
            'id_espacio' => $ctx['espacioAId'],
            'dia_semana' => null,
            'fecha_especifica' => $specificDate,
            'hora_inicio' => '08:00:00',
            'hora_fin' => '09:00:00',
            'cupo_maximo' => 10,
            'observaciones' => null,
            'estado' => 'activo',
            'creado_por' => $ctx['usuarioId'],
            'semana_inicio' => $specificDate,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertTrue(DeporteHorario::overlaps(
            $ctx['espacioBId'],
            null,
            '08:30:00',
            '09:30:00',
            null,
            $specificDate,
            $specificDate
        ));
    }
}
