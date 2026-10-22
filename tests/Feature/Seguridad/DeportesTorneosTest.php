<?php

namespace Tests\Feature\Seguridad;

use App\Models\Deporte\AsistenciaDeportiva;
use App\Models\Deporte\DeporteHorario;
use App\Models\Deporte\InscripcionDeportiva;
use App\Models\Deporte\SesionAsistencia;
use App\Models\Persona;
use App\Models\Usuario;
use App\Support\Deportes\ServicioInscripcion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DeportesTorneosTest extends SeguridadTestCase
{
    private Usuario $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->crearUsuario('Administrador');
        $this->actingAs($this->admin)->withSession(['master_key_validated' => true, 'ultima_actividad' => time(), 'sede_activa' => $this->sedeA()]);
    }

    private function espacio(string $nombre = 'Cancha de prueba'): int
    {
        return DB::table('espacio_deportivo')->insertGetId([
            'nombre_epd' => $nombre . ' ' . random_int(1000, 999999),
            'sed_id'     => $this->sedeA(),
            'status_id'  => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_epd');
    }

    private function actividad(): int
    {
        return DB::table('actividad_deportiva')->insertGetId([
            'nombre_acd' => 'Actividad ' . random_int(1000, 999999),
            'tipo_acd'   => 'programada',
            'status_id'  => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_acd');
    }

    private function calendario(): int
    {
        return DB::table('calendario_deportivo')->insertGetId([
            'nombre_cld'       => 'Calendario ' . random_int(1000, 999999),
            'fecha_inicio_cld' => Carbon::today()->subDays(30)->toDateString(),
            'fecha_fin_cld'    => Carbon::today()->addDays(180)->toDateString(),
            'sed_id'           => $this->sedeA(),
            'use_id'           => $this->admin->id_use,
            'status_id'        => 1,
            'created_at'       => now(),
            'updated_at'       => now(),
        ], 'id_cld');
    }

    private function horario(array $datos = []): int
    {
        return DB::table('horario_deportivo')->insertGetId($datos + [
            'dia_semana_hde'    => DeporteHorario::diaDeFecha(Carbon::today()),
            'semana_inicio_hde' => Carbon::today()->subDays(14)->startOfWeek(Carbon::MONDAY)->toDateString(),
            'hora_inicio_hde'   => '15:00:00',
            'hora_fin_hde'      => '15:45:00',
            'cupo_maximo_hde'   => null,
            'estado_hde'        => 'activo',
            'cld_id'            => $datos['cld_id'] ?? $this->calendario(),
            'acd_id'            => $datos['acd_id'] ?? $this->actividad(),
            'epd_id'            => $datos['epd_id'] ?? $this->espacio(),
            'use_id'            => $this->admin->id_use,
            'status_id'         => 1,
            'created_at'        => now(),
            'updated_at'        => now(),
        ], 'id_hde');
    }

    private function persona(): Persona
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

    private function inscritosVigentes(int $horario): int
    {
        return DB::table('inscripcion_deportiva')->where('hde_id', $horario)->where('estado_ide', '!=', 'retirado')->whereNull('deleted_at')->count();
    }

    public function test_la_inscripcion_respeta_el_cupo_maximo(): void
    {
        $horario = $this->horario(['cupo_maximo_hde' => 2]);

        foreach ([$this->persona(), $this->persona()] as $persona) {
            $this->post(route('admin.deportes.inscripciones.store'), ['hde_id' => $horario, 'cedula' => $persona->cedula_per])
                ->assertSessionHasNoErrors();
        }

        $tercera = $this->persona();
        $this->post(route('admin.deportes.inscripciones.store'), ['hde_id' => $horario, 'cedula' => $tercera->cedula_per])
            ->assertSessionHasErrors('cedula');

        $this->assertSame(2, $this->inscritosVigentes($horario));
        $this->assertFalse(DB::table('inscripcion_deportiva')->where('hde_id', $horario)->where('per_id', $tercera->id_per)->exists());
    }

    public function test_una_persona_no_se_inscribe_dos_veces_en_el_mismo_horario(): void
    {
        $horario = $this->horario();
        $persona = $this->persona();

        $this->post(route('admin.deportes.inscripciones.store'), ['hde_id' => $horario, 'cedula' => $persona->cedula_per])
            ->assertSessionHasNoErrors();
        $this->post(route('admin.deportes.inscripciones.store'), ['hde_id' => $horario, 'cedula' => 'V-' . $persona->cedula_per])
            ->assertSessionHasErrors('cedula');

        $this->assertSame(1, DB::table('inscripcion_deportiva')->where('hde_id', $horario)->where('per_id', $persona->id_per)->count());
    }

    public function test_una_cedula_inexistente_es_rechazada(): void
    {
        $horario = $this->horario();

        do {
            $cedula = (string) random_int(10000000, 99999999);
        } while (DB::table('persona')->where('cedula_per', $cedula)->exists());

        $this->post(route('admin.deportes.inscripciones.store'), ['hde_id' => $horario, 'cedula' => $cedula])
            ->assertSessionHasErrors('cedula');

        $this->assertSame(0, $this->inscritosVigentes($horario));
    }

    public function test_un_retirado_libera_el_cupo_y_puede_reinscribirse(): void
    {
        $horario = $this->horario(['cupo_maximo_hde' => 1]);
        $persona = $this->persona();
        $otra = $this->persona();

        $inscripcion = ServicioInscripcion::inscribir(DeporteHorario::query()->findOrFail($horario), $persona->cedula_per);

        $this->delete(route('admin.deportes.inscripciones.destroy', $inscripcion->id_ide))->assertSessionHasNoErrors();
        $this->post(route('admin.deportes.inscripciones.store'), ['hde_id' => $horario, 'cedula' => $otra->cedula_per])->assertSessionHasNoErrors();
        $this->post(route('admin.deportes.inscripciones.store'), ['hde_id' => $horario, 'cedula' => $persona->cedula_per])->assertSessionHasErrors('cedula');

        $this->assertSame(1, $this->inscritosVigentes($horario));
    }

    public function test_al_cerrar_la_asistencia_los_no_marcados_quedan_inasistentes(): void
    {
        $horario = $this->horario();
        $modelo = DeporteHorario::query()->findOrFail($horario);
        $inscripciones = collect([$this->persona(), $this->persona(), $this->persona()])
            ->map(fn ($persona) => ServicioInscripcion::inscribir($modelo, $persona->cedula_per));

        $hoy = Carbon::today()->toDateString();

        $this->post(route('admin.deportes.asistencia.store'), ['hde_id' => $horario, 'fecha' => $hoy])->assertSessionHasNoErrors();

        $sesion = SesionAsistencia::query()->where('hde_id', $horario)->whereDate('fecha_sad', $hoy)->firstOrFail();
        $presente = $inscripciones->first()->id_ide;

        $this->post(route('admin.deportes.asistencia.cerrar', $sesion->id_sad), ['marcas_enviadas' => 1, 'presentes' => [$presente]])
            ->assertSessionHasNoErrors();

        $estados = DB::table('asistencia_deportiva')->where('sad_id', $sesion->id_sad)->pluck('estado_asd', 'ide_id');

        $this->assertCount(3, $estados);
        $this->assertSame(AsistenciaDeportiva::ESTADO_PRESENTE, $estados[$presente]);
        foreach ($inscripciones->slice(1) as $inscripcion) {
            $this->assertSame(AsistenciaDeportiva::ESTADO_INASISTENTE, $estados[$inscripcion->id_ide]);
        }

        $this->assertTrue((bool) DB::table('sesion_asistencia_deportiva')->where('id_sad', $sesion->id_sad)->value('cerrada_sad'));

        $this->put(route('admin.deportes.asistencia.update', $sesion->id_sad), ['presentes' => $inscripciones->pluck('id_ide')->all()])
            ->assertSessionHasErrors('sesion');

        $this->assertSame(1, DB::table('asistencia_deportiva')->where('sad_id', $sesion->id_sad)->where('estado_asd', 'presente')->count());
    }

    public function test_no_se_abre_asistencia_en_un_dia_sin_sesion(): void
    {
        $horario = $this->horario(['dia_semana_hde' => DeporteHorario::diaDeFecha(Carbon::today()->subDay())]);

        $this->post(route('admin.deportes.asistencia.store'), ['hde_id' => $horario, 'fecha' => Carbon::today()->toDateString()])
            ->assertSessionHasErrors('fecha');

        $this->assertFalse(DB::table('sesion_asistencia_deportiva')->where('hde_id', $horario)->exists());
    }

    public function test_los_carteles_pdf_responden_como_pdf(): void
    {
        $horario = $this->horario(['cupo_maximo_hde' => 16]);

        $this->post(route('admin.deportes.torneos.store'), [
            'nombre_tde'    => 'Copa de prueba',
            'modalidad_tde' => 'Mixto',
            'hde_id'        => $horario,
        ])->assertSessionHasNoErrors();

        $torneo = DB::table('torneo_deportivo')->where('hde_id', $horario)->value('id_tde');
        $this->assertNotNull($torneo);

        $respuesta = $this->get(route('admin.deportes.torneos.cartel_pdf', $torneo));
        $respuesta->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $respuesta->headers->get('content-type'));

        $respuesta = $this->get(route('admin.deportes.maestros.horarios.cartel_pdf', $horario));
        $respuesta->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $respuesta->headers->get('content-type'));
    }

    public function test_detecta_solape_de_horarios_en_el_mismo_espacio(): void
    {
        $calendario = $this->calendario();
        $actividad = $this->actividad();
        $espacio = $this->espacio('Cancha A');
        $otroEspacio = $this->espacio('Cancha B');
        $semana = Carbon::today()->startOfWeek(Carbon::MONDAY)->addWeek()->toDateString();

        $this->horario([
            'cld_id'            => $calendario,
            'acd_id'            => $actividad,
            'epd_id'            => $espacio,
            'dia_semana_hde'    => 'lunes',
            'semana_inicio_hde' => $semana,
            'hora_inicio_hde'   => '08:15:00',
            'hora_fin_hde'      => '09:00:00',
        ]);

        $datos = [
            'tipo_horario'  => 'recurrente',
            'cld_id'        => $calendario,
            'acd_id'        => $actividad,
            'semana_inicio' => $semana,
            'bloques'       => ['lunes|08:15|09:00'],
        ];

        $this->post(route('admin.deportes.maestros.horarios.store'), $datos + ['epd_id' => $espacio])
            ->assertSessionHasErrors('bloques');
        $this->assertSame(1, DB::table('horario_deportivo')->where('epd_id', $espacio)->count());

        $this->post(route('admin.deportes.maestros.horarios.store'), $datos + ['epd_id' => $otroEspacio])
            ->assertSessionHasNoErrors();
        $this->assertSame(1, DB::table('horario_deportivo')->where('epd_id', $otroEspacio)->count());

        $desde = Carbon::parse($semana);
        $hasta = Carbon::today()->addDays(180);

        $this->assertNotNull(DeporteHorario::buscarSolape($espacio, 'lunes', $desde, $hasta, '08:30', '09:30'));
        $this->assertNull(DeporteHorario::buscarSolape($espacio, 'lunes', $desde, $hasta, '09:00', '09:45'));
        $this->assertNull(DeporteHorario::buscarSolape($espacio, 'martes', $desde, $hasta, '08:30', '09:30'));
        $this->assertNull(DeporteHorario::buscarSolape($espacio, 'lunes', $desde->copy()->subWeeks(3), $desde->copy()->subDay(), '08:30', '09:30'));
    }

    public function test_las_pantallas_de_deportes_cargan(): void
    {
        $horario = $this->horario(['cupo_maximo_hde' => 5]);
        ServicioInscripcion::inscribir(DeporteHorario::query()->findOrFail($horario), $this->persona()->cedula_per);

        foreach ([
            route('admin.deportes.espacios.index'),
            route('admin.deportes.espacios.create'),
            route('admin.deportes.actividades.index'),
            route('admin.deportes.calendarios.index'),
            route('admin.deportes.torneos.index'),
            route('admin.deportes.torneos.create'),
            route('admin.deportes.inscripciones.index', ['hde_id' => $horario]),
            route('admin.deportes.asistencia.index', ['hde_id' => $horario]),
            route('admin.deportes.asistencia.reporte', ['hde_id' => $horario]),
            route('admin.deportes.maestros.horarios.index'),
            route('admin.deportes.maestros.horarios.create'),
            route('admin.deportes.maestros.horarios.show', $horario),
            route('admin.deportes.maestros.horarios.edit', $horario),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
