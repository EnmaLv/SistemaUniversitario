<?php

namespace Tests\Feature\Seguridad;

use App\Models\Usuario;
use App\Models\salud\Consulta;
use Illuminate\Support\Facades\DB;

class ConsultaFechaRealTest extends SeguridadTestCase
{
    private Usuario $admin;

    private array $base;

    protected function setUp(): void
    {
        parent::setUp();

        $sede = $this->sedeA();
        $this->admin = $this->crearUsuario('Administrador');
        $consultorio = DB::table('consultorio')->insertGetId(['nombre_csr' => 'Consultorio fecha real', 'sed_id' => $sede, 'created_at' => now(), 'updated_at' => now()], 'id_csr');
        $enfermedad = DB::table('enfermedad')->insertGetId(['codigo_efm' => 'FR' . random_int(100, 99999), 'nombre_efm' => 'Gripe de prueba', 'categoria_efm' => 'fisica', 'created_at' => now(), 'updated_at' => now()], 'id_efm');

        $this->base = [
            'per_id'         => $this->crearUsuario([], 'Secreta123', [], $sede)->persona->id_per,
            'medico_id'      => $this->crearUsuario([], 'Secreta123', [], $sede)->persona->id_per,
            'consultorio_id' => $consultorio,
            'motivo'         => 'Fiebre',
            'diagnostico'    => 'Cuadro viral',
            'enfermedades'   => [$enfermedad],
        ];

        $this->actingAs($this->admin)->withSession(['master_key_validated' => true, 'ultima_actividad' => time(), 'sede_activa' => $sede]);
    }

    public function test_fecha_pasada_sin_motivo_es_rechazada(): void
    {
        $this->post(route('admin.salud.movimientos.consultas.store'), $this->base + [
            'fecha' => now()->subDays(3)->toDateString(),
        ])->assertSessionHasErrors('motivo_registro_tardio');

        $this->assertFalse(Consulta::todasLasSedes()->where('paciente_per_id', $this->base['per_id'])->exists());
    }

    public function test_fecha_pasada_con_motivo_se_guarda_y_queda_en_bitacora(): void
    {
        $fecha = now()->subDays(3)->toDateString();

        $this->post(route('admin.salud.movimientos.consultas.store'), $this->base + [
            'fecha'                  => $fecha,
            'motivo_registro_tardio' => 'Jornada sin conexión, se cargó después',
        ])->assertSessionHasNoErrors();

        $consulta = Consulta::todasLasSedes()->where('paciente_per_id', $this->base['per_id'])->firstOrFail();

        $this->assertSame($fecha, $consulta->fecha_cme->toDateString());
        $this->assertSame(now()->toDateString(), $consulta->created_at->toDateString());
        $this->assertSame('Jornada sin conexión, se cargó después', $consulta->motivo_registro_tardio_cme);
        $this->assertTrue($consulta->esRegistroTardio());

        $evento = DB::table('bitacora_sistema')
            ->where('tipo_evento_bis', 'consulta_registro_tardio')
            ->where('tabla_afectada_bis', 'consulta_medica')
            ->where('registro_id_bis', $consulta->id_cme)
            ->first();

        $this->assertNotNull($evento);
        $this->assertStringContainsString('Jornada sin conexión', (string) $evento->observacion_bis);

        $this->get(route('admin.salud.movimientos.consultas.show', $consulta))
            ->assertOk()
            ->assertSee('Fecha de registro')
            ->assertSee('Jornada sin conexión, se cargó después');
    }

    public function test_fecha_de_hoy_no_exige_motivo_ni_registra_tardio(): void
    {
        $this->post(route('admin.salud.movimientos.consultas.store'), $this->base + [
            'fecha' => now()->toDateString(),
        ])->assertSessionHasNoErrors();

        $consulta = Consulta::todasLasSedes()->where('paciente_per_id', $this->base['per_id'])->firstOrFail();

        $this->assertNull($consulta->motivo_registro_tardio_cme);
        $this->assertFalse(DB::table('bitacora_sistema')->where('tipo_evento_bis', 'consulta_registro_tardio')->where('registro_id_bis', $consulta->id_cme)->exists());
    }

    public function test_fecha_futura_es_rechazada(): void
    {
        $this->post(route('admin.salud.movimientos.consultas.store'), $this->base + [
            'fecha'                  => now()->addDay()->toDateString(),
            'motivo_registro_tardio' => 'No debería aceptarse',
        ])->assertSessionHasErrors('fecha');

        $this->assertFalse(Consulta::todasLasSedes()->where('paciente_per_id', $this->base['per_id'])->exists());
    }

    public function test_al_editar_se_compara_con_la_fecha_de_registro(): void
    {
        $consulta = Consulta::todasLasSedes()->create([
            'paciente_per_id' => $this->base['per_id'], 'medico_per_id' => $this->base['medico_id'], 'csr_id' => $this->base['consultorio_id'],
            'use_id' => $this->admin->id_use, 'fecha_cme' => now()->subDays(10)->toDateString(),
            'motivo_cme' => 'Control', 'observaciones_cme' => '', 'diagnostico_cme' => 'Sano',
        ]);
        DB::table('consulta_medica')->where('id_cme', $consulta->id_cme)->update(['created_at' => now()->subDays(10)]);

        $this->put(route('admin.salud.movimientos.consultas.update', $consulta), $this->base + [
            'fecha' => now()->subDays(10)->toDateString(),
        ])->assertSessionHasNoErrors();

        $this->put(route('admin.salud.movimientos.consultas.update', $consulta), $this->base + [
            'fecha' => now()->subDays(12)->toDateString(),
        ])->assertSessionHasErrors('motivo_registro_tardio');
    }
}
