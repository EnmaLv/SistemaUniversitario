<?php

namespace Tests\Feature\Seguridad;

use App\Models\Deporte\DeporteCalendario;
use App\Models\Deporte\DeporteHorario;
use App\Models\Deporte\SesionAsistencia;
use App\Models\Usuario;
use App\Support\Deportes\ServicioInscripcion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DeportesTorneosTest extends SeguridadTestCase
{
    use DatosDeportes;

    private Usuario $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->crearUsuario('Administrador');
        $this->actingAs($this->admin)->withSession(['master_key_validated' => true, 'ultima_actividad' => time(), 'sede_activa' => $this->sedeA()]);
    }

    private function inscritosVigentes(int $bloque): int
    {
        return DB::table('inscripcion_deportiva')->where('hde_id', $bloque)->where('estado_ide', '!=', 'retirado')->whereNull('deleted_at')->count();
    }

    private function datosTorneo(int $disciplina, int $espacio, array $extra = []): array
    {
        return array_merge([
            'nombre_tde'       => 'Copa de prueba ' . random_int(1000, 999999),
            'modalidad_tde'    => 'Mixto',
            'acd_id'           => $disciplina,
            'epd_id'           => $espacio,
            'fecha_inicio_cld' => Carbon::yesterday()->toDateString(),
            'fecha_fin_cld'    => Carbon::tomorrow()->toDateString(),
            'hora_inicio'      => '14:00',
            'hora_fin'         => '17:00',
        ], $extra);
    }

    private function crearTorneo(array $datos): int
    {
        $this->post(route('admin.deportes.torneos.store'), $datos)->assertSessionHasNoErrors();

        return (int) DB::table('torneo_deportivo')->where('nombre_tde', $datos['nombre_tde'])->value('id_tde');
    }

    public function test_la_inscripcion_individual_respeta_el_cupo(): void
    {
        $actividad = $this->esporadica(Carbon::today()->addDays(3), [$this->bloque($this->disciplina(), $this->espacio(), '08:00', '10:00', 2)]);
        $bloque = $this->primerBloque($actividad)->id_hde;

        foreach ([$this->persona(), $this->persona()] as $persona) {
            $this->post(route('admin.deportes.inscripciones.store'), ['hde_id' => $bloque, 'cedula' => $persona->cedula_per])
                ->assertSessionHasNoErrors();
        }

        $tercera = $this->persona();
        $this->post(route('admin.deportes.inscripciones.store'), ['hde_id' => $bloque, 'cedula' => $tercera->cedula_per])
            ->assertSessionHasErrors('cedula');

        $this->assertSame(2, $this->inscritosVigentes($bloque));
        $this->assertFalse(DB::table('inscripcion_deportiva')->where('hde_id', $bloque)->where('per_id', $tercera->id_per)->exists());
    }

    public function test_la_inscripcion_rechaza_duplicados_cedulas_inexistentes_y_libera_cupo_al_retirar(): void
    {
        $actividad = $this->esporadica(Carbon::today()->addDays(3), [$this->bloque($this->disciplina(), $this->espacio(), '08:00', '10:00', 1)]);
        $bloque = $this->primerBloque($actividad);
        $persona = $this->persona();
        $otra = $this->persona();

        $this->post(route('admin.deportes.inscripciones.store'), ['hde_id' => $bloque->id_hde, 'cedula' => $this->cedulaInexistente()])
            ->assertSessionHasErrors('cedula');

        $inscripcion = ServicioInscripcion::inscribir($bloque, $persona->cedula_per);

        $this->post(route('admin.deportes.inscripciones.store'), ['hde_id' => $bloque->id_hde, 'cedula' => 'V-' . $persona->cedula_per])
            ->assertSessionHasErrors('cedula');

        $this->delete(route('admin.deportes.inscripciones.destroy', $inscripcion->id_ide))->assertSessionHasNoErrors();
        $this->post(route('admin.deportes.inscripciones.store'), ['hde_id' => $bloque->id_hde, 'cedula' => $otra->cedula_per])->assertSessionHasNoErrors();
        $this->post(route('admin.deportes.inscripciones.store'), ['hde_id' => $bloque->id_hde, 'cedula' => $persona->cedula_per])->assertSessionHasErrors('cedula');

        $this->assertSame(1, $this->inscritosVigentes($bloque->id_hde));
    }

    public function test_una_actividad_abierta_no_admite_inscripciones(): void
    {
        $actividad = $this->esporadica(Carbon::today()->addDays(3), [$this->bloque($this->disciplina(), $this->espacio())], false);

        $this->post(route('admin.deportes.inscripciones.store'), ['hde_id' => $this->primerBloque($actividad)->id_hde, 'cedula' => $this->persona()->cedula_per])
            ->assertSessionHasErrors('hde_id');
    }

    public function test_el_choque_de_espacio_se_rechaza_entre_actividades_distintas(): void
    {
        $disciplina = $this->disciplina();
        $cancha = $this->espacio('Cancha A');
        $otraCancha = $this->espacio('Cancha B');
        $lunes = Carbon::today()->next(Carbon::MONDAY);

        $this->esporadica($lunes, [$this->bloque($disciplina, $cancha, '08:00', '10:00')]);

        $datos = [
            'nombre_cld'       => 'Entrenamiento que choca',
            'tipo_cld'         => 'recurrente',
            'dias'             => ['lunes', 'jueves'],
            'fecha_inicio_cld' => $lunes->copy()->subWeek()->toDateString(),
            'fecha_fin_cld'    => $lunes->copy()->addWeeks(2)->toDateString(),
        ];

        $this->post(route('admin.deportes.calendarios.store'), $datos + ['bloques' => [$this->bloque($disciplina, $cancha, '09:00', '11:00')]])
            ->assertSessionHasErrors('bloques');
        $this->assertFalse(DB::table('calendario_deportivo')->where('nombre_cld', 'Entrenamiento que choca')->exists());

        $this->post(route('admin.deportes.calendarios.store'), $datos + ['bloques' => [$this->bloque($disciplina, $cancha, '10:00', '11:00')]])
            ->assertSessionHasNoErrors();

        $this->post(route('admin.deportes.calendarios.store'), array_merge($datos, ['nombre_cld' => 'Otro entrenamiento', 'bloques' => [$this->bloque($disciplina, $otraCancha, '08:30', '09:30')]]))
            ->assertSessionHasNoErrors();

        $choque = DeporteHorario::buscarChoque($cancha, [$lunes->toDateString()], '09:30', '10:30');
        $this->assertNotNull($choque);
        $this->assertNull(DeporteHorario::buscarChoque($cancha, [$lunes->copy()->addDay()->toDateString()], '09:30', '10:30'));
        $this->assertNull(DeporteHorario::buscarChoque($cancha, [$lunes->toDateString()], '11:00', '12:00'));
    }

    public function test_una_jornada_admite_bloques_simultaneos_en_espacios_distintos_y_rechaza_el_mismo_espacio(): void
    {
        $fecha = Carbon::today()->addDays(4)->toDateString();
        $canchas = [$this->espacio('Cancha 1'), $this->espacio('Cancha 2'), $this->espacio('Cancha 3')];
        $disciplinas = [$this->disciplina('Fútbol'), $this->disciplina('Baloncesto'), $this->disciplina('Voleibol')];

        $base = [
            'nombre_cld'  => 'Jornada sección 631',
            'tipo_cld'    => 'esporadica',
            'fecha'       => $fecha,
            'seccion_cld' => '631',
        ];

        $this->post(route('admin.deportes.calendarios.store'), $base + ['bloques' => [
            $this->bloque($disciplinas[0], $canchas[0], '07:00', '12:00'),
            $this->bloque($disciplinas[1], $canchas[0], '09:00', '11:00'),
        ]])->assertSessionHasErrors('bloques');

        $this->post(route('admin.deportes.calendarios.store'), $base + ['bloques' => [
            $this->bloque($disciplinas[0], $canchas[0], '07:00', '12:00'),
            $this->bloque($disciplinas[1], $canchas[1], '07:00', '12:00'),
            $this->bloque($disciplinas[2], $canchas[2], '07:00', '12:00'),
        ]])->assertSessionHasNoErrors();

        $actividad = DeporteCalendario::query()->where('nombre_cld', 'Jornada sección 631')->firstOrFail();
        $this->assertSame(DeporteCalendario::TIPO_ESPORADICA, $actividad->tipo_cld);
        $this->assertSame('631', $actividad->seccion_cld);
        $this->assertCount(3, $this->bloquesDe($actividad));
        $this->assertSame([$fecha], $actividad->fechas());

        $this->post(route('admin.deportes.calendarios.store'), array_merge($base, ['nombre_cld' => 'Otra jornada', 'bloques' => [
            $this->bloque($disciplinas[0], $canchas[2], '11:00', '13:00'),
        ]]))->assertSessionHasErrors('bloques');
    }

    public function test_torneo_con_integrantes_por_cedula_y_sin_inscripcion_directa(): void
    {
        $disciplina = $this->disciplina('Baloncesto');
        $espacio = $this->espacio();
        $torneo = $this->crearTorneo($this->datosTorneo($disciplina, $espacio));
        $bloque = (int) DB::table('torneo_deportivo')->where('id_tde', $torneo)->value('hde_id');

        $this->assertSame('torneo', DB::table('calendario_deportivo')->where('id_cld', DB::table('horario_deportivo')->where('id_hde', $bloque)->value('cld_id'))->value('tipo_cld'));

        foreach (['Los Halcones', 'Las Panteras'] as $nombre) {
            $this->post(route('admin.deportes.torneos.equipos.store', $torneo), ['nombre_equipo_etd' => $nombre])->assertSessionHasNoErrors();
        }

        $equipos = DB::table('equipo_torneo_deportivo')->where('tde_id', $torneo)->orderBy('id_etd')->pluck('id_etd')->all();
        [$a, $b, $c] = [$this->persona(), $this->persona(), $this->persona()];

        $this->post(route('admin.deportes.torneos.integrantes.store', $equipos[0]), ['cedula' => $a->cedula_per . ', ' . $b->cedula_per])
            ->assertSessionHasNoErrors();
        $this->post(route('admin.deportes.torneos.integrantes.store', $equipos[1]), ['cedula' => $c->cedula_per])
            ->assertSessionHasNoErrors();
        $this->post(route('admin.deportes.torneos.integrantes.store', $equipos[1]), ['cedula' => $a->cedula_per])
            ->assertSessionHasErrors('cedula');
        $this->post(route('admin.deportes.torneos.integrantes.store', $equipos[1]), ['cedula' => $this->cedulaInexistente()])
            ->assertSessionHasErrors('cedula');

        $this->assertSame(2, DB::table('integrante_equipo_deporte')->where('etd_id', $equipos[0])->count());
        $this->assertSame(1, DB::table('integrante_equipo_deporte')->where('etd_id', $equipos[1])->count());

        $this->post(route('admin.deportes.inscripciones.store'), ['hde_id' => $bloque, 'cedula' => $this->persona()->cedula_per])
            ->assertSessionHasErrors('hde_id');
        $this->post(route('admin.deportes.inscripciones.store'), ['tde_id' => $torneo, 'cedula' => $this->persona()->cedula_per])
            ->assertSessionHasErrors('hde_id');
        $this->assertSame(0, DB::table('inscripcion_deportiva')->where('hde_id', $bloque)->count());

        $this->get(route('admin.deportes.torneos.show', $torneo))->assertOk()->assertSee('Cómo inscribir un equipo')->assertSee('Los Halcones');

        $ayer = Carbon::yesterday()->toDateString();
        $this->post(route('admin.deportes.asistencia.store'), ['hde_id' => $bloque, 'fecha' => $ayer])->assertSessionHasNoErrors();
        $sesion = SesionAsistencia::query()->where('hde_id', $bloque)->where('fecha_sad', $ayer)->firstOrFail();

        $respuesta = $this->postJson(route('admin.deportes.asistencia.store_presentes', $sesion->id_sad), ['cedulas' => $a->cedula_per . "\n" . $this->persona()->cedula_per]);
        $respuesta->assertOk();
        $this->assertSame(['marcada', 'no_inscrita'], array_column($respuesta->json('resultados'), 'resultado'));
        $this->assertSame(3, $respuesta->json('estado.total_inscritos'));

        $this->post(route('admin.deportes.asistencia.store_cierre', $sesion->id_sad))->assertSessionHasNoErrors();
        $this->assertSame(2, DB::table('asistencia_deportiva')->where('sad_id', $sesion->id_sad)->where('estado_asd', 'inasistente')->whereNotNull('ied_id')->count());
    }

    public function test_cambiar_el_estado_del_torneo_libera_o_completa_su_espacio(): void
    {
        $espacio = $this->espacio();
        $datos = $this->datosTorneo($this->disciplina(), $espacio, ['fecha_inicio_cld' => Carbon::today()->addDays(10)->toDateString(), 'fecha_fin_cld' => Carbon::today()->addDays(12)->toDateString()]);
        $torneo = $this->crearTorneo($datos);
        $bloque = (int) DB::table('torneo_deportivo')->where('id_tde', $torneo)->value('hde_id');
        $fecha = Carbon::today()->addDays(11)->toDateString();

        $this->assertNotNull(DeporteHorario::buscarChoque($espacio, [$fecha], '15:00', '16:00'));

        $this->patch(route('admin.deportes.torneos.update_estado', $torneo), ['estado_tde' => 'cancelado'])->assertSessionHasNoErrors();

        $this->assertSame('cancelado', DB::table('horario_deportivo')->where('id_hde', $bloque)->value('estado_hde'));
        $this->assertNull(DeporteHorario::buscarChoque($espacio, [$fecha], '15:00', '16:00'));
        $this->assertTrue(DB::table('bitacora_sistema')->where('tipo_evento_bis', 'cambio_estado_torneo')->where('registro_id_bis', $torneo)->exists());
    }

    public function test_los_carteles_pdf_responden_como_pdf(): void
    {
        $actividad = $this->esporadica(Carbon::today()->addDays(5), [
            $this->bloque($this->disciplina(), $this->espacio(), '07:00', '12:00', 20),
            $this->bloque($this->disciplina(), $this->espacio(), '07:00', '12:00'),
        ]);

        $torneo = $this->crearTorneo($this->datosTorneo($this->disciplina(), $this->espacio()));

        foreach ([route('admin.deportes.torneos.cartel_pdf', $torneo), route('admin.deportes.calendarios.cartel_pdf', $actividad->id_cld), route('admin.deportes.maestros.horarios.exportarPdf')] as $url) {
            $respuesta = $this->get($url);
            $respuesta->assertOk();
            $this->assertStringContainsString('application/pdf', (string) $respuesta->headers->get('content-type'));
        }
    }

    public function test_las_pantallas_de_deportes_cargan(): void
    {
        $espacio = $this->espacio();
        $actividad = $this->esporadica(Carbon::today(), [$this->bloque($this->disciplina(), $espacio, '08:00', '09:00', 5)]);
        $bloque = $this->primerBloque($actividad);
        ServicioInscripcion::inscribir($bloque, $this->persona()->cedula_per);
        $recurrente = $this->recurrente(Carbon::today()->subWeek(), Carbon::today()->addWeek(), ['lunes', 'miercoles'], [$this->bloque($this->disciplina(), $espacio, '16:00', '17:00')]);
        $torneo = $this->crearTorneo($this->datosTorneo($this->disciplina(), $this->espacio()));

        foreach ([
            route('admin.deportes.espacios.index'),
            route('admin.deportes.espacios.create'),
            route('admin.deportes.actividades.index'),
            route('admin.deportes.actividades.create'),
            route('admin.deportes.calendarios.index'),
            route('admin.deportes.calendarios.create'),
            route('admin.deportes.calendarios.show', $actividad->id_cld),
            route('admin.deportes.calendarios.edit', $actividad->id_cld),
            route('admin.deportes.calendarios.show', $recurrente->id_cld),
            route('admin.deportes.torneos.index'),
            route('admin.deportes.torneos.create'),
            route('admin.deportes.torneos.show', $torneo),
            route('admin.deportes.torneos.edit', $torneo),
            route('admin.deportes.inscripciones.index'),
            route('admin.deportes.inscripciones.index', ['hde_id' => $bloque->id_hde]),
            route('admin.deportes.asistencia.index'),
            route('admin.deportes.asistencia.index', ['fecha' => Carbon::yesterday()->toDateString(), 'q' => 'prueba']),
            route('admin.deportes.asistencia.historial', $recurrente->id_cld),
            route('admin.deportes.maestros.horarios.index'),
            route('admin.deportes.maestros.horarios.index', ['epd_id' => $espacio, 'semana' => Carbon::today()->addWeek()->toDateString()]),
        ] as $url) {
            $this->get($url)->assertOk();
        }

        $this->get(route('admin.deportes.asistencia.index'))->assertSee($actividad->nombre_cld);
        $this->get(route('admin.deportes.maestros.horarios.index'))->assertSee($actividad->nombre_cld);
    }

    public function test_editar_una_actividad_conserva_los_bloques_con_inscritos(): void
    {
        $disciplina = $this->disciplina();
        $espacio = $this->espacio();
        $actividad = $this->esporadica(Carbon::today()->addDays(6), [
            $this->bloque($disciplina, $espacio, '08:00', '10:00', 10),
            $this->bloque($disciplina, $this->espacio(), '08:00', '10:00'),
        ]);
        [$primero, $segundo] = $this->bloquesDe($actividad)->all();
        ServicioInscripcion::inscribir($primero, $this->persona()->cedula_per);

        $datos = [
            'nombre_cld' => $actividad->nombre_cld,
            'tipo_cld'   => 'esporadica',
            'fecha'      => Carbon::today()->addDays(7)->toDateString(),
            'con_inscripcion_cld' => 1,
        ];

        $this->put(route('admin.deportes.calendarios.update', $actividad->id_cld), $datos + ['bloques' => [$this->bloque($disciplina, $espacio, '09:00', '11:00', 10, $segundo->id_hde)]])
            ->assertSessionHasErrors('bloques');

        $this->put(route('admin.deportes.calendarios.update', $actividad->id_cld), $datos + ['bloques' => [$this->bloque($disciplina, $espacio, '09:00', '11:00', 10, $primero->id_hde)]])
            ->assertSessionHasNoErrors();

        $this->assertSame('09:00', substr((string) DB::table('horario_deportivo')->where('id_hde', $primero->id_hde)->value('hora_inicio_hde'), 0, 5));
        $this->assertNotNull(DB::table('horario_deportivo')->where('id_hde', $segundo->id_hde)->value('deleted_at'));
        $this->assertSame(Carbon::today()->addDays(7)->toDateString(), Carbon::parse(DB::table('calendario_deportivo')->where('id_cld', $actividad->id_cld)->value('fecha_inicio_cld'))->toDateString());
    }
}
