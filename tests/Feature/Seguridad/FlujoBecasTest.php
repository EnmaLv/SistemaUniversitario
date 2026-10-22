<?php

namespace Tests\Feature\Seguridad;

use App\Models\Becas\SolicitudBeca;
use App\Models\Perfil;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FlujoBecasTest extends SeguridadTestCase
{
    public function test_postulacion_aprobacion_y_descarga_del_documento(): void
    {
        Storage::fake('local');

        $admin = $this->crearUsuario('Administrador');
        $estudiante = $this->crearUsuario(['Becario'], 'Secreta123', [], $this->sedeA(), Perfil::ESTUDIANTE);
        $otro = $this->crearUsuario(['Becario'], 'Secreta123', [], $this->sedeA(), Perfil::ESTUDIANTE);

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

        $this->actingAs($estudiante)
            ->post(route('admin.becas.solicitudes.store'), [
                'per_id'         => $estudiante->per_id,
                'jornada_id'     => $jornada,
                'id_beneficio'   => $beneficio,
                'id_lapso'       => $lapso,
                'tipo_solicitud' => 'nueva',
                'archivo_notas'  => UploadedFile::fake()->create('notas.pdf', 100, 'application/pdf'),
                'respuestas'     => [['id_pregunta' => $pregunta, 'valor' => '1200']],
            ])
            ->assertSessionHasNoErrors();

        $solicitud = SolicitudBeca::where('per_id', $estudiante->per_id)->firstOrFail();
        $this->assertSame(0, $solicitud->estado_solicitud_sob);
        $this->assertSame($beneficio, (int) $solicitud->bes_id);
        $this->assertSame('1200', DB::table('respuesta_solicitud_beneficio')->where('sob_id', $solicitud->id_sob)->value('valor_rsb'));

        $this->actingAs($admin)->withSession(['master_key_validated' => true, 'ultima_actividad' => time()])
            ->put(route('admin.becas.solicitudes.verificar', $solicitud->id_sob), ['estado' => 1])
            ->assertSessionHasNoErrors();

        $this->assertSame(1, $solicitud->fresh()->estado_solicitud_sob);
        $this->assertSame(1, (int) DB::table('jornada_beneficio')->where('id_jnb', $jornada)->value('cupos_asignados_jnb'));
        $this->assertSame($admin->id_use, (int) $solicitud->fresh()->verificado_por_use_id);

        $this->actingAs($estudiante)->get(route('admin.becas.solicitudes.descargar_documento', $solicitud->id_sob))->assertOk();
        $this->actingAs($otro)->get(route('admin.becas.solicitudes.descargar_documento', $solicitud->id_sob))->assertForbidden();
    }
}
