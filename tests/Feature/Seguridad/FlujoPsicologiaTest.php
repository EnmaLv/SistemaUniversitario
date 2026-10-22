<?php

namespace Tests\Feature\Seguridad;

use App\Models\salud\Cita;
use Illuminate\Support\Facades\DB;

class FlujoPsicologiaTest extends SeguridadTestCase
{
    public function test_solicitud_confirmacion_y_nota_de_evolucion_de_una_cita(): void
    {
        $psicologo = $this->crearUsuario('Psicólogo');
        $paciente = $this->crearUsuario('Paciente');

        $this->actingAs($paciente)
            ->post(route('admin.psicologia.maestros.citas.store'), [
                'psicologo_id'          => $psicologo->id_use,
                'fecha_solicitada'      => now()->addDays(2)->toDateString(),
                'motivo'                => 'Necesito orientación',
                'bloques_sugeridos_cps' => 'Lunes 09:00 - 10:00',
            ])
            ->assertSessionHasNoErrors();

        $cita = Cita::where('paciente_use_id', $paciente->id_use)->where('psicologo_use_id', $psicologo->id_use)->firstOrFail();
        $this->assertSame('pendiente', $cita->estado_cita_cps);

        $this->actingAs($psicologo)
            ->patchJson(route('admin.psicologia.maestros.citas.accept', $cita->id_cps), [
                'fecha'  => now()->addDays(2)->toDateString(),
                'hora'   => '09:00',
                'bloque' => 'Lunes 09:00 - 10:00',
            ])
            ->assertOk();

        $this->assertSame('confirmada', $cita->fresh()->estado_cita_cps);

        DB::table('plantilla_historia_clinica')->insert([
            'psicologo_use_id' => $psicologo->id_use,
            'titulo_phc'       => 'Esquema general',
            'secciones_phc'    => json_encode([]),
            'estado_phc'       => 1,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $this->actingAs($psicologo)
            ->patch(route('admin.psicologia.maestros.citas.update.notas', $cita->id_cps), [
                'structured'           => 1,
                'esa_id'               => DB::table('estado_animo')->whereNull('deleted_at')->value('id_esa'),
                'estado_animo_detalle' => 'Tranquilo',
                'avance_estado'        => DB::table('avance_sesion')->whereNull('deleted_at')->value('id_avs'),
                'avance_detalle'       => 'Buen avance',
                'campos_dinamicos'     => [],
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('realizada', $cita->fresh()->estado_cita_cps);

        $this->actingAs($psicologo)
            ->get(route('admin.psicologia.maestros.historias.show', $paciente->id_use))
            ->assertOk();

        $this->assertTrue(DB::table('historia_clinica')->where('paciente_use_id', $paciente->id_use)->where('psicologo_use_id', $psicologo->id_use)->exists());
    }
}
