<?php

namespace Tests\Feature\Seguridad;

use App\Models\salud\Cita;
use App\Models\salud\HistoriaClinica;
use App\Models\Usuario;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class ConfidencialidadPsicologiaTest extends SeguridadTestCase
{
    private function crearPaciente(string $nombre): Usuario
    {
        $paciente = $this->crearUsuario('Paciente');
        $paciente->persona->update(['nombres_per' => $nombre]);

        return $paciente->fresh();
    }

    private function crearCita(Usuario $paciente, Usuario $psicologo, string $estado = 'pendiente'): Cita
    {
        return Cita::create([
            'user_id'      => $paciente->id_use,
            'psicologo_id' => $psicologo->id_use,
            'fecha'        => now()->addDays(3)->toDateString(),
            'hora'         => $estado === 'pendiente' ? null : '09:00:00',
            'estado'       => $estado,
            'prioridad'    => 'media',
            'motivo'       => Crypt::encryptString('Motivo confidencial'),
        ]);
    }

    public function test_un_psicologo_no_ve_ni_modifica_las_citas_de_otro(): void
    {
        $psicologoA = $this->crearUsuario('Psicólogo');
        $psicologoB = $this->crearUsuario('Psicólogo');
        $citaDeB = $this->crearCita($this->crearPaciente('PacienteDeB'), $psicologoB);

        $this->actingAs($psicologoA);

        $this->getJson(route('admin.psicologia.maestros.citas.show.json', $citaDeB->id))->assertForbidden();
        $this->patchJson(route('admin.psicologia.maestros.citas.accept', $citaDeB->id), [
            'fecha'  => now()->addDays(3)->toDateString(),
            'hora'   => '09:00',
            'bloque' => 'Lunes 09:00 - 10:00',
        ])->assertForbidden();
        $this->patchJson(route('admin.psicologia.maestros.citas.reject', $citaDeB->id))->assertForbidden();
        $this->get(route('admin.psicologia.maestros.citas.edit.note', $citaDeB->id))->assertForbidden();

        $this->assertSame('pendiente', $citaDeB->fresh()->estado);
    }

    public function test_cada_psicologo_ve_solo_sus_citas_pendientes(): void
    {
        $psicologoA = $this->crearUsuario('Psicólogo');
        $psicologoB = $this->crearUsuario('Psicólogo');
        $this->crearCita($this->crearPaciente('PacienteDeA'), $psicologoA);
        $this->crearCita($this->crearPaciente('PacienteDeB'), $psicologoB);

        $this->actingAs($psicologoA)
            ->get(route('admin.psicologia.maestros.agenda.pending.list'))
            ->assertOk()
            ->assertSee('PacienteDeA')
            ->assertDontSee('PacienteDeB');

        $this->assertSame(
            [$psicologoA->id_use],
            Cita::obtenerCitasGlobales(null, 50, $psicologoA->id_use)->getCollection()->pluck('psicologo_id')->unique()->values()->all()
        );
    }

    public function test_un_psicologo_no_abre_la_historia_de_un_paciente_de_otro(): void
    {
        $psicologoA = $this->crearUsuario('Psicólogo');
        $psicologoB = $this->crearUsuario('Psicólogo');
        $paciente = $this->crearPaciente('PacienteDeB');
        $this->crearCita($paciente, $psicologoB, 'realizada');

        $this->actingAs($psicologoA);

        $this->get(route('admin.psicologia.maestros.historias.show', $paciente->id_use))->assertForbidden();
        $this->get(route('admin.psicologia.maestros.historias.reportePdf', $paciente->id_use))->assertForbidden();
        $this->get(route('admin.psicologia.maestros.historias.downloadZip', $paciente->id_use))->assertForbidden();

        $this->assertFalse(HistoriaClinica::where('user_id', $paciente->id_use)->exists());
    }

    public function test_dos_psicologos_con_el_mismo_paciente_tienen_historias_separadas(): void
    {
        $psicologoA = $this->crearUsuario('Psicólogo');
        $psicologoB = $this->crearUsuario('Psicólogo');
        $paciente = $this->crearPaciente('PacienteCompartido');
        $this->crearCita($paciente, $psicologoA, 'realizada');
        $this->crearCita($paciente, $psicologoB, 'realizada');

        $historiaA = HistoriaClinica::iniciarHistoria($paciente->id_use, $psicologoA->id_use);
        $historiaB = HistoriaClinica::iniciarHistoria($paciente->id_use, $psicologoB->id_use);

        $this->assertNotSame($historiaA->id, $historiaB->id);

        $seccionDeA = DB::table('historia_secciones_personalizadas')->insertGetId([
            'historia_clinica_id' => $historiaA->id,
            'titulo'              => 'Sección privada de A',
            'orden'               => 1,
            'status'              => 1,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        $this->actingAs($psicologoB)
            ->delete(route('admin.psicologia.maestros.historias.secciones.destroy', $seccionDeA))
            ->assertForbidden();

        $this->assertTrue(DB::table('historia_secciones_personalizadas')->where('id', $seccionDeA)->exists());
    }

    public function test_un_administrador_no_puede_leer_datos_clinicos(): void
    {
        $administrador = $this->crearUsuario('Administrador');
        $psicologo = $this->crearUsuario('Psicólogo');
        $paciente = $this->crearPaciente('PacienteConfidencial');
        $cita = $this->crearCita($paciente, $psicologo, 'realizada');

        $this->assertTrue($administrador->esSuperAdmin());

        $this->actingAs($administrador);

        $this->get(route('admin.psicologia.maestros.agenda.index'))->assertForbidden();
        $this->get(route('admin.psicologia.maestros.historias.index'))->assertForbidden();
        $this->get(route('admin.psicologia.maestros.historias.show', $paciente->id_use))->assertForbidden();
        $this->getJson(route('admin.psicologia.maestros.citas.show.json', $cita->id))->assertForbidden();
        $this->get(route('admin.psicologia.maestros.agenda.estadisticas', ['format' => 'json']))->assertForbidden();
    }

    public function test_las_estadisticas_generales_solo_tienen_numeros_agregados(): void
    {
        $administrador = $this->crearUsuario('Administrador');
        $psicologo = $this->crearUsuario('Psicólogo');
        $this->crearCita($this->crearPaciente('NombreQueNoDebeSalir'), $psicologo, 'realizada');

        $respuesta = $this->actingAs($administrador)
            ->getJson(route('admin.psicologia.estadisticas.generales', [
                'start_date' => now()->subMonth()->toDateString(),
                'end_date'   => now()->addMonth()->toDateString(),
            ]))
            ->assertOk()
            ->assertJsonMissingPath('resumen.avances_pacientes')
            ->assertJsonMissingPath('resumen.prioridades_pacientes')
            ->assertJsonMissingPath('resumen.estados_animo_pacientes');

        $this->assertStringNotContainsString('NombreQueNoDebeSalir', $respuesta->getContent());
        $this->assertSame(1, $respuesta->json('resumen.total_citas'));
    }

    public function test_el_paciente_solo_gestiona_sus_propias_citas(): void
    {
        $psicologo = $this->crearUsuario('Psicólogo');
        $duenio = $this->crearPaciente('Duenio');
        $otro = $this->crearPaciente('Otro');
        $cita = $this->crearCita($duenio, $psicologo);

        $this->actingAs($otro)
            ->patchJson(route('admin.psicologia.maestros.citas.cancel', $cita->id))
            ->assertForbidden();
        $this->assertSame('pendiente', $cita->fresh()->estado);

        $this->actingAs($duenio)
            ->patchJson(route('admin.psicologia.maestros.citas.cancel', $cita->id))
            ->assertOk()
            ->assertJson(['status' => 'success']);
        $this->assertSame('cancelada', $cita->fresh()->estado);
        $this->assertSame('paciente', $cita->fresh()->cancelado_por);
    }

    public function test_el_paciente_no_puede_fijar_la_prioridad_de_su_cita(): void
    {
        $psicologo = $this->crearUsuario('Psicólogo');
        $paciente = $this->crearPaciente('PacientePrioridad');

        $this->actingAs($paciente)
            ->post(route('admin.psicologia.maestros.citas.store'), [
                'psicologo_id'      => $psicologo->id_use,
                'fecha_solicitada'  => now()->addDays(2)->toDateString(),
                'motivo'            => 'Necesito orientación',
                'bloques_sugeridos' => 'Lunes 09:00 - 10:00',
                'prioridad'         => 'crítica',
            ])
            ->assertRedirect(route('admin.psicologia.maestros.citas.index'));

        $this->assertNotSame('crítica', Cita::where('user_id', $paciente->id_use)->value('prioridad'));
    }
}
