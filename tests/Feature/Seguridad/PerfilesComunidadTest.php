<?php

namespace Tests\Feature\Seguridad;

use App\Livewire\Archivos;
use App\Models\Archivo;
use App\Models\Becas\SolicitudBeca;
use App\Models\Bitacora as BitacoraModelo;
use App\Models\Perfil;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\Usuario;
use App\Support\Cuentas\CuentasComunidad;
use App\Support\Permisos\VersionPermisos;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

class PerfilesComunidadTest extends SeguridadTestCase
{
    private function crearPersona(string $perfil, ?string $cedula = null, string $apellido = 'PRUEBA'): Persona
    {
        return Persona::create([
            'nombres_per'   => 'PERSONA',
            'apellidos_per' => $apellido,
            'cedula_per'    => $cedula ?? (string) random_int(10000000, 29999999),
            'telefono_per'  => '04140000000',
            'genero_per'    => 'FEMENINO',
            'correo_per'    => 'persona' . random_int(1, 999999) . '@prueba.test',
            'id_perfil'     => Perfil::idPorNombre($perfil),
            'sed_id'        => $this->sedeA(),
        ]);
    }

    private function slugsDe(Usuario $usuario): array
    {
        return $usuario->roles()->pluck('slug_rol')->sort()->values()->all();
    }

    public function test_el_seeder_registra_los_perfiles_de_la_comunidad(): void
    {
        $this->assertSame(1, Perfil::idPorNombre(Perfil::USUARIO));
        $this->assertSame(2, Perfil::idEstudiante());

        foreach (Perfil::NOMBRES as $nombre) {
            $this->assertNotNull(Perfil::idPorNombre($nombre), "Falta el perfil {$nombre}");
        }

        $this->assertSame(Perfil::idPorNombre(Perfil::COMUNIDAD), Perfil::idPorNombre('comunidad UNIVERSITARIA'));
    }

    public function test_un_docente_recibe_solo_el_rol_paciente(): void
    {
        $usuario = CuentasComunidad::asegurarCuenta($this->crearPersona(Perfil::DOCENTE));

        $this->assertSame(['paciente'], $this->slugsDe($usuario));
        $this->assertSame(Perfil::idPorNombre(Perfil::DOCENTE), (int) $usuario->id_perfil);
    }

    public function test_un_estudiante_recibe_paciente_y_becario(): void
    {
        $usuario = CuentasComunidad::asegurarCuenta($this->crearPersona(Perfil::ESTUDIANTE));

        $this->assertSame(['becario', 'paciente'], $this->slugsDe($usuario));
    }

    public function test_cambiar_el_perfil_ajusta_becario_sin_tocar_los_demas_roles(): void
    {
        $persona = $this->crearPersona(Perfil::ESTUDIANTE);
        $usuario = CuentasComunidad::asegurarCuenta($persona);
        $usuario->roles()->attach(Rol::where('slug_rol', 'obrero')->value('id_rol'));

        $this->assertSame(['becario', 'obrero', 'paciente'], $this->slugsDe($usuario));

        $version = VersionPermisos::actual();
        $persona->fresh()->update(['id_perfil' => Perfil::idPorNombre(Perfil::DOCENTE)]);

        $this->assertSame(['obrero', 'paciente'], $this->slugsDe($usuario));
        $this->assertSame(Perfil::idPorNombre(Perfil::DOCENTE), (int) $usuario->fresh()->id_perfil);
        $this->assertGreaterThan($version, VersionPermisos::actual());

        $evento = BitacoraModelo::where('tipo_evento_bis', 'rol_becario_por_perfil')
            ->where('registro_id_bis', $persona->id_per)
            ->latest('id_bis')
            ->first();
        $this->assertNotNull($evento);

        $persona->fresh()->update(['id_perfil' => Perfil::idEstudiante()]);

        $this->assertSame(['becario', 'obrero', 'paciente'], $this->slugsDe($usuario));
        $this->assertSame(2, BitacoraModelo::where('tipo_evento_bis', 'rol_becario_por_perfil')->where('registro_id_bis', $persona->id_per)->count());
    }

    public function test_actualizar_otros_campos_no_toca_los_roles(): void
    {
        $persona = $this->crearPersona(Perfil::DOCENTE);
        $usuario = CuentasComunidad::asegurarCuenta($persona);
        $usuario->roles()->attach(Rol::where('slug_rol', 'becario')->value('id_rol'));

        $persona->fresh()->update(['apellidos_per' => 'CAMBIADO']);

        $this->assertSame(['becario', 'paciente'], $this->slugsDe($usuario));
    }

    public function test_la_importacion_respeta_el_perfil_de_cada_fila(): void
    {
        Storage::fake('local');

        $existente = $this->crearPersona(Perfil::ESTUDIANTE, '24777001');
        $cuentaExistente = CuentasComunidad::asegurarCuenta($existente);
        $this->assertSame(['becario', 'paciente'], $this->slugsDe($cuentaExistente));

        $csv = implode("\n", [
            'Cedula,Nombre,Apellido,Semestre,PNF,Tipo de persona',
            '24777101,Ana,Estudiante,2,Informática,estudiante',
            '24777102,Luis,Docente,3,Informática,DOCENTE',
            '24777103,Rosa,Administrativa,,,ADMINISTRATÍVO',
            '24777104,Juan,Obrero,,,Obrero',
            '24777105,Eva,Comunidad,,,Comunidad Universitaria',
            '24777106,Leo,SinPerfil,1,Informática,',
            '24777107,Max,Invalido,,,Astronauta',
            '24777001,Persona,Existente,,,docente',
        ]);

        Livewire::actingAs($this->crearUsuario('Administrador'))
            ->test(Archivos::class)
            ->set('archivo', UploadedFile::fake()->createWithContent('personas.csv', $csv))
            ->call('save')
            ->assertHasNoErrors();

        $resumen = Archivo::latest('id')->first()->resumen;

        $this->assertSame(8, $resumen['total_filas']);
        $this->assertSame(6, $resumen['personas_creadas']);
        $this->assertSame(1, $resumen['personas_actualizadas']);
        $this->assertSame(1, $resumen['filas_con_error']);
        $this->assertSame(1, $resumen['perfiles_cambiados']);
        $this->assertStringContainsString('Perfil no válido: «Astronauta»', $resumen['errores'][0]['motivo']);

        $perfiles = Persona::whereIn('cedula_per', ['24777101', '24777102', '24777103', '24777104', '24777105', '24777106', '24777001'])
            ->pluck('id_perfil', 'cedula_per')
            ->map(fn ($id) => (int) $id)
            ->all();

        $esperados = [
            '24777101' => Perfil::idEstudiante(),
            '24777102' => Perfil::idPorNombre(Perfil::DOCENTE),
            '24777103' => Perfil::idPorNombre(Perfil::ADMINISTRATIVO),
            '24777104' => Perfil::idPorNombre(Perfil::OBRERO),
            '24777105' => Perfil::idPorNombre(Perfil::COMUNIDAD),
            '24777106' => Perfil::idEstudiante(),
            '24777001' => Perfil::idPorNombre(Perfil::DOCENTE),
        ];
        ksort($esperados);
        ksort($perfiles);
        $this->assertSame($esperados, $perfiles);

        $this->assertFalse(Persona::where('cedula_per', '24777107')->exists());

        $docente = Persona::where('cedula_per', '24777102')->first();
        $estudiante = Persona::where('cedula_per', '24777101')->first();
        $this->assertNull($docente->semestre_per);
        $this->assertSame('2', (string) $estudiante->semestre_per);
        $this->assertFalse(DB::table('persona_programa')->where('per_id', $docente->id_per)->exists());

        if (DB::table('programa')->where('status_id', 1)->exists()) {
            $this->assertTrue(DB::table('persona_programa')->where('per_id', $estudiante->id_per)->exists());
        }

        $this->assertSame(['paciente'], $this->slugsDe(Usuario::where('username_use', '24777102')->firstOrFail()));
        $this->assertSame(['becario', 'paciente'], $this->slugsDe(Usuario::where('username_use', '24777101')->firstOrFail()));
        $this->assertSame(['paciente'], $this->slugsDe($cuentaExistente->fresh()));
    }

    public function test_un_docente_no_puede_solicitar_una_beca(): void
    {
        Storage::fake('local');

        $docente = $this->crearUsuario(['Becario'], 'Secreta123', [], $this->sedeA(), Perfil::DOCENTE);
        $estudiante = $this->crearUsuario(['Becario'], 'Secreta123', [], $this->sedeA(), Perfil::ESTUDIANTE);

        $beneficio = DB::table('beneficio_estudiantil')->insertGetId([
            'nombre_beneficio_bes' => 'Beca de prueba ' . random_int(1, 99999), 'slug_bes' => 'beca-prueba-' . random_int(1, 99999),
            'cupones_disponibles_bes' => 10, 'cupones_ocupados_bes' => 0, 'created_at' => now(), 'updated_at' => now(),
        ], 'id_bes');
        $pregunta = DB::table('pregunta_beneficio')->insertGetId([
            'codigo_pgb' => 'ingreso', 'etiqueta_pgb' => 'Ingreso familiar', 'tipo_campo_pgb' => 'number', 'obligatoria_pgb' => false,
            'orden_pgb' => 1, 'bes_id' => $beneficio, 'created_at' => now(), 'updated_at' => now(),
        ], 'id_pgb');
        $lapso = DB::table('lapso_academico')->insertGetId([
            'codigo_laa' => 'P' . random_int(1000, 9999), 'fecha_inicio_laa' => now()->subMonth()->toDateString(),
            'es_actual_laa' => true, 'permite_solicitudes_laa' => true, 'created_at' => now(), 'updated_at' => now(),
        ], 'id_laa');
        $jornada = DB::table('jornada_beneficio')->insertGetId([
            'nombre_jornada_jnb' => 'Jornada de prueba', 'fecha_inicio_solicitud_jnb' => now()->subDay()->toDateString(),
            'fecha_fin_solicitud_jnb' => now()->addWeek()->toDateString(), 'cupos_maximos_jnb' => 5, 'cupos_asignados_jnb' => 0,
            'bes_id' => $beneficio, 'laa_id' => $lapso, 'created_at' => now(), 'updated_at' => now(),
        ], 'id_jnb');

        $this->actingAs($docente)
            ->post(route('admin.becas.solicitudes.store'), [
                'per_id'         => $docente->per_id,
                'jornada_id'     => $jornada,
                'id_beneficio'   => $beneficio,
                'id_lapso'       => $lapso,
                'tipo_solicitud' => 'nueva',
                'archivo_notas'  => UploadedFile::fake()->create('notas.pdf', 100, 'application/pdf'),
                'respuestas'     => [['id_pregunta' => $pregunta, 'valor' => '1200']],
            ])
            ->assertSessionHasErrors(['per_id' => 'Solo las personas con perfil Estudiante pueden solicitar becas.']);

        $this->assertFalse(SolicitudBeca::where('per_id', $docente->per_id)->exists());

        $this->actingAs($docente)
            ->get(route('admin.becas.solicitar'))
            ->assertForbidden()
            ->assertSee('solo están disponibles para personas con perfil Estudiante');

        $estudiantes = Persona::estudiantes()->pluck('id_per')->all();
        $this->assertContains($estudiante->per_id, $estudiantes);
        $this->assertNotContains($docente->per_id, $estudiantes);
    }
}
