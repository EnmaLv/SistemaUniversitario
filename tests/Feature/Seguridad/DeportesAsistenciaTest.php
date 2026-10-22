<?php

namespace Tests\Feature\Seguridad;

use App\Models\Deporte\AsistenciaDeportiva;
use App\Models\Deporte\SesionAsistencia;
use App\Models\Usuario;
use App\Support\Deportes\ServicioAsistencia;
use App\Support\Deportes\ServicioInscripcion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DeportesAsistenciaTest extends SeguridadTestCase
{
    use DatosDeportes;

    private Usuario $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->crearUsuario('Administrador');
        $this->actingAs($this->admin)->withSession(['master_key_validated' => true, 'ultima_actividad' => time(), 'sede_activa' => $this->sedeA()]);
    }

    private function abrir(int $bloque, string $fecha): SesionAsistencia
    {
        $this->post(route('admin.deportes.asistencia.store'), ['hde_id' => $bloque, 'fecha' => $fecha])->assertSessionHasNoErrors();

        return SesionAsistencia::query()->where('hde_id', $bloque)->where('fecha_sad', $fecha)->firstOrFail();
    }

    public function test_se_registra_la_asistencia_de_un_evento_de_ayer(): void
    {
        $ayer = Carbon::yesterday()->toDateString();
        $actividad = $this->esporadica($ayer, [$this->bloque($this->disciplina(), $this->espacio(), '17:00', '19:00')]);
        $bloque = $this->primerBloque($actividad);
        $inscrito = $this->persona();
        ServicioInscripcion::inscribir($bloque, $inscrito->cedula_per);

        $this->get(route('admin.deportes.asistencia.index'))->assertOk()->assertSee($actividad->nombre_cld)->assertSee('Ayer');

        $respuesta = $this->post(route('admin.deportes.asistencia.store'), ['hde_id' => $bloque->id_hde, 'fecha' => $ayer]);
        $sesion = SesionAsistencia::query()->where('hde_id', $bloque->id_hde)->where('fecha_sad', $ayer)->firstOrFail();
        $respuesta->assertRedirect(route('admin.deportes.asistencia.show', $sesion->id_sad));

        $this->get(route('admin.deportes.asistencia.show', $sesion->id_sad))->assertOk()->assertSee('Cédula de la persona presente');

        $this->post(route('admin.deportes.asistencia.store_presentes', $sesion->id_sad), ['cedulas' => 'V-' . $inscrito->cedula_per])
            ->assertRedirect(route('admin.deportes.asistencia.show', $sesion->id_sad))
            ->assertSessionHas('resultados_asistencia', fn ($resultados) => $resultados[0]['resultado'] === ServicioAsistencia::RESULTADO_MARCADA);

        $this->assertSame(AsistenciaDeportiva::ESTADO_PRESENTE, DB::table('asistencia_deportiva')->where('sad_id', $sesion->id_sad)->where('per_id', $inscrito->id_per)->value('estado_asd'));

        $this->post(route('admin.deportes.asistencia.store'), ['hde_id' => $bloque->id_hde, 'fecha' => Carbon::tomorrow()->toDateString()])
            ->assertSessionHasErrors('fecha');
        $this->post(route('admin.deportes.asistencia.store'), ['hde_id' => $bloque->id_hde, 'fecha' => Carbon::today()->subDays(2)->toDateString()])
            ->assertSessionHasErrors('fecha');
    }

    public function test_pegar_cinco_cedulas_con_una_inexistente(): void
    {
        $actividad = $this->esporadica(Carbon::today(), [$this->bloque($this->disciplina(), $this->espacio(), '07:00', '09:00')]);
        $bloque = $this->primerBloque($actividad);
        $personas = collect(range(1, 4))->map(fn () => $this->persona());
        ServicioInscripcion::inscribir($bloque, $personas[0]->cedula_per);
        ServicioInscripcion::inscribir($bloque, $personas[1]->cedula_per);
        ServicioInscripcion::inscribir($bloque, $personas[2]->cedula_per);
        $inexistente = $this->cedulaInexistente();

        $sesion = $this->abrir($bloque->id_hde, Carbon::today()->toDateString());
        $texto = $personas[0]->cedula_per . "\n" . $personas[1]->cedula_per . ', ' . $inexistente . "\r\n" . 'V-' . $personas[2]->cedula_per . ';' . $personas[3]->cedula_per;

        $respuesta = $this->postJson(route('admin.deportes.asistencia.store_presentes', $sesion->id_sad), ['cedulas' => $texto]);
        $respuesta->assertOk();

        $resultados = collect($respuesta->json('resultados'))->keyBy('cedula');
        $this->assertCount(5, $resultados);
        $this->assertSame(ServicioAsistencia::RESULTADO_NO_ENCONTRADA, $resultados[$inexistente]['resultado']);
        $this->assertSame(ServicioAsistencia::RESULTADO_NO_INSCRITA, $resultados[$personas[3]->cedula_per]['resultado']);
        $this->assertSame(ServicioAsistencia::RESULTADO_MARCADA, $resultados[$personas[2]->cedula_per]['resultado']);
        $this->assertSame(4, $respuesta->json('estado.total_presentes'));
        $this->assertSame(3, $respuesta->json('estado.presentes_inscritos'));
        $this->assertSame(3, $respuesta->json('estado.total_inscritos'));
        $this->assertSame(4, DB::table('asistencia_deportiva')->where('sad_id', $sesion->id_sad)->where('estado_asd', 'presente')->count());
        $this->assertNull(DB::table('asistencia_deportiva')->where('sad_id', $sesion->id_sad)->where('per_id', $personas[3]->id_per)->value('ide_id'));

        $otra = $this->postJson(route('admin.deportes.asistencia.store_presentes', $sesion->id_sad), ['cedulas' => $personas[0]->cedula_per]);
        $this->assertSame(ServicioAsistencia::RESULTADO_YA_MARCADA, $otra->json('resultados.0.resultado'));
        $this->assertSame(4, DB::table('asistencia_deportiva')->where('sad_id', $sesion->id_sad)->count());
    }

    public function test_al_cerrar_los_inscritos_no_marcados_quedan_inasistentes(): void
    {
        $actividad = $this->esporadica(Carbon::yesterday(), [$this->bloque($this->disciplina(), $this->espacio(), '15:00', '16:00')]);
        $bloque = $this->primerBloque($actividad);
        $inscripciones = collect([$this->persona(), $this->persona(), $this->persona()])
            ->map(fn ($persona) => ServicioInscripcion::inscribir($bloque, $persona->cedula_per));
        $presente = $inscripciones->first();

        $sesion = $this->abrir($bloque->id_hde, Carbon::yesterday()->toDateString());
        $this->postJson(route('admin.deportes.asistencia.store_presentes', $sesion->id_sad), ['cedulas' => DB::table('persona')->where('id_per', $presente->per_id)->value('cedula_per')])->assertOk();

        $this->post(route('admin.deportes.asistencia.store_cierre', $sesion->id_sad))
            ->assertRedirect(route('admin.deportes.asistencia.reporte', $sesion->id_sad));

        $estados = DB::table('asistencia_deportiva')->where('sad_id', $sesion->id_sad)->pluck('estado_asd', 'per_id');
        $this->assertCount(3, $estados);
        $this->assertSame(AsistenciaDeportiva::ESTADO_PRESENTE, $estados[$presente->per_id]);

        foreach ($inscripciones->slice(1) as $inscripcion) {
            $this->assertSame(AsistenciaDeportiva::ESTADO_INASISTENTE, $estados[$inscripcion->per_id]);
            $this->assertSame('no_asistio', DB::table('inscripcion_deportiva')->where('id_ide', $inscripcion->id_ide)->value('estado_ide'));
        }

        $this->assertSame('asistio', DB::table('inscripcion_deportiva')->where('id_ide', $presente->id_ide)->value('estado_ide'));
        $this->assertTrue((bool) DB::table('sesion_asistencia_deportiva')->where('id_sad', $sesion->id_sad)->value('cerrada_sad'));
        $this->assertTrue(DB::table('bitacora_sistema')->where('tipo_evento_bis', 'cierre_asistencia_deportiva')->where('registro_id_bis', $sesion->id_sad)->exists());

        $this->postJson(route('admin.deportes.asistencia.store_presentes', $sesion->id_sad), ['cedulas' => $this->persona()->cedula_per])->assertStatus(422);
        $this->assertSame(1, DB::table('asistencia_deportiva')->where('sad_id', $sesion->id_sad)->where('estado_asd', 'presente')->count());

        $this->get(route('admin.deportes.asistencia.reporte', $sesion->id_sad))->assertOk()->assertSee('Inasistentes');
        $respuesta = $this->get(route('admin.deportes.asistencia.reporte_pdf', $sesion->id_sad));
        $respuesta->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $respuesta->headers->get('content-type'));
    }

    public function test_reabrir_la_asistencia_queda_en_bitacora(): void
    {
        $actividad = $this->esporadica(Carbon::yesterday(), [$this->bloque($this->disciplina(), $this->espacio(), '10:00', '11:00')]);
        $bloque = $this->primerBloque($actividad);
        $ausente = $this->persona();
        ServicioInscripcion::inscribir($bloque, $ausente->cedula_per);

        $sesion = $this->abrir($bloque->id_hde, Carbon::yesterday()->toDateString());
        $this->post(route('admin.deportes.asistencia.store_cierre', $sesion->id_sad))->assertSessionHasNoErrors();
        $this->assertSame(1, DB::table('asistencia_deportiva')->where('sad_id', $sesion->id_sad)->where('estado_asd', 'inasistente')->count());

        $this->post(route('admin.deportes.asistencia.reabrir', $sesion->id_sad), ['motivo' => 'Faltó marcar a una persona'])
            ->assertRedirect(route('admin.deportes.asistencia.show', $sesion->id_sad))
            ->assertSessionHasNoErrors();

        $this->assertFalse((bool) DB::table('sesion_asistencia_deportiva')->where('id_sad', $sesion->id_sad)->value('cerrada_sad'));
        $this->assertSame(0, DB::table('asistencia_deportiva')->where('sad_id', $sesion->id_sad)->count());
        $this->assertSame('inscrito', DB::table('inscripcion_deportiva')->where('hde_id', $bloque->id_hde)->where('per_id', $ausente->id_per)->value('estado_ide'));

        $registro = DB::table('bitacora_sistema')->where('tipo_evento_bis', 'reapertura_asistencia_deportiva')->where('registro_id_bis', $sesion->id_sad)->first();
        $this->assertNotNull($registro);
        $this->assertSame($this->admin->id_use, (int) $registro->use_id);
        $this->assertStringContainsString('Faltó marcar', (string) $registro->observacion_bis);

        $this->postJson(route('admin.deportes.asistencia.store_presentes', $sesion->id_sad), ['cedulas' => $ausente->cedula_per])->assertOk();
        $this->post(route('admin.deportes.asistencia.store_cierre', $sesion->id_sad))->assertSessionHasNoErrors();
        $this->assertSame('presente', DB::table('asistencia_deportiva')->where('sad_id', $sesion->id_sad)->where('per_id', $ausente->id_per)->value('estado_asd'));

        $this->post(route('admin.deportes.asistencia.reabrir', $sesion->id_sad))->assertSessionHasNoErrors();
        $this->post(route('admin.deportes.asistencia.reabrir', $sesion->id_sad))->assertSessionHasErrors('sesion');
    }

    public function test_actividad_recurrente_con_tres_sesiones_y_su_reporte_por_persona(): void
    {
        $hoy = Carbon::today();
        $actividad = $this->recurrente($hoy->copy()->subDays(2), $hoy->copy()->addDays(10), array_values(\App\Models\Deporte\DeporteCalendario::DIAS), [
            $this->bloque($this->disciplina(), $this->espacio(), '18:00', '19:30', 10),
        ]);
        $bloque = $this->primerBloque($actividad);
        $constante = $this->persona();
        $ocasional = $this->persona();
        ServicioInscripcion::inscribir($bloque, $constante->cedula_per);
        ServicioInscripcion::inscribir($bloque, $ocasional->cedula_per);

        foreach ([2, 1, 0] as $diasAtras) {
            $fecha = $hoy->copy()->subDays($diasAtras)->toDateString();
            $sesion = $this->abrir($bloque->id_hde, $fecha);
            $cedulas = $diasAtras === 1 ? $constante->cedula_per . ',' . $ocasional->cedula_per : $constante->cedula_per;
            $this->postJson(route('admin.deportes.asistencia.store_presentes', $sesion->id_sad), ['cedulas' => $cedulas])->assertOk();
            $this->post(route('admin.deportes.asistencia.store_cierre', $sesion->id_sad))->assertSessionHasNoErrors();
        }

        $this->assertSame(3, SesionAsistencia::query()->where('hde_id', $bloque->id_hde)->where('cerrada_sad', true)->count());

        $historial = ServicioAsistencia::historial($actividad);
        $filas = $historial['filas']->keyBy('id_per');
        $this->assertSame(3, $historial['totalSesiones']);
        $this->assertSame(3, $filas[$constante->id_per]->presentes);
        $this->assertSame(0, $filas[$constante->id_per]->inasistentes);
        $this->assertSame(1, $filas[$ocasional->id_per]->presentes);
        $this->assertSame(2, $filas[$ocasional->id_per]->inasistentes);
        $this->assertEquals(33.3, $filas[$ocasional->id_per]->porcentaje);

        $this->get(route('admin.deportes.asistencia.historial', $actividad->id_cld))
            ->assertOk()
            ->assertSee('Asistió 3 de 3 sesiones')
            ->assertSee('Asistió 1 de 3 sesiones');

        $respuesta = $this->get(route('admin.deportes.asistencia.historial_pdf', $actividad->id_cld));
        $respuesta->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $respuesta->headers->get('content-type'));

        $esporadica = $this->esporadica($hoy, [$this->bloque($this->disciplina(), $this->espacio())]);
        $this->get(route('admin.deportes.asistencia.historial', $esporadica->id_cld))
            ->assertRedirect(route('admin.deportes.calendarios.show', $esporadica->id_cld))
            ->assertSessionHas('error');
    }

    public function test_quitar_un_presente_por_error(): void
    {
        $actividad = $this->esporadica(Carbon::today(), [$this->bloque($this->disciplina(), $this->espacio(), '12:00', '13:00')], false);
        $bloque = $this->primerBloque($actividad);
        $persona = $this->persona();
        $sesion = $this->abrir($bloque->id_hde, Carbon::today()->toDateString());

        $respuesta = $this->postJson(route('admin.deportes.asistencia.store_presentes', $sesion->id_sad), ['cedulas' => $persona->cedula_per]);
        $this->assertSame(ServicioAsistencia::RESULTADO_MARCADA, $respuesta->json('resultados.0.resultado'));
        $this->assertFalse($respuesta->json('estado.usa_inscritos'));

        $marca = DB::table('asistencia_deportiva')->where('sad_id', $sesion->id_sad)->where('per_id', $persona->id_per)->value('id_asd');
        $this->postJson(route('admin.deportes.asistencia.store_quitar', $sesion->id_sad), ['asistencia_id' => $marca])
            ->assertOk()
            ->assertJsonPath('estado.total_presentes', 0);

        $this->post(route('admin.deportes.asistencia.store_cierre', $sesion->id_sad))->assertSessionHasNoErrors();
        $this->assertSame(0, DB::table('asistencia_deportiva')->where('sad_id', $sesion->id_sad)->count());
    }
}
