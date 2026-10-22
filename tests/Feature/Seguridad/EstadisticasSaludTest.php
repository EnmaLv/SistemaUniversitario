<?php

namespace Tests\Feature\Seguridad;

use App\Models\Perfil;
use App\Models\salud\Consulta;
use App\Services\Salud\SaludHomeService;
use Illuminate\Support\Facades\DB;

class EstadisticasSaludTest extends SeguridadTestCase
{
    private function paciente(?string $nacimiento, string $perfil, ?int $programa = null): int
    {
        $persona = $this->crearUsuario([], 'Secreta123', [], $this->sedeA(), $perfil)->persona;

        DB::table('persona')->where('id_per', $persona->id_per)->update(['fecha_nacimiento_per' => $nacimiento]);

        if ($programa !== null) {
            DB::table('persona_programa')->insert([
                'per_id' => $persona->id_per, 'prg_id' => $programa, 'fecha_inicio_pep' => '2023-01-01',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        return $persona->id_per;
    }

    private function consulta(int $paciente, int $medico, int $consultorio, int $usuario, string $fecha): void
    {
        Consulta::todasLasSedes()->create([
            'paciente_per_id' => $paciente, 'medico_per_id' => $medico, 'csr_id' => $consultorio, 'use_id' => $usuario,
            'fecha_cme' => $fecha, 'motivo_cme' => 'Control', 'observaciones_cme' => '', 'diagnostico_cme' => 'Sano',
        ]);
    }

    public function test_estadisticas_por_edad_al_consultar_pnf_y_tipo_de_persona(): void
    {
        $sede = $this->sedeA();
        $admin = $this->crearUsuario('Administrador');
        $medico = $this->crearUsuario([], 'Secreta123', [], $sede)->persona->id_per;
        $consultorio = DB::table('consultorio')->insertGetId(['nombre_csr' => 'Consultorio estadísticas', 'sed_id' => $sede, 'created_at' => now(), 'updated_at' => now()], 'id_csr');
        $programa = DB::table('programa')->insertGetId(['codigo_prg' => 'PRB-' . random_int(1000, 99999), 'nombre_prg' => 'PNF DE PRUEBA ESTADISTICAS', 'created_at' => now(), 'updated_at' => now()], 'id_prg');

        $menor = $this->paciente('2006-06-16', Perfil::ESTUDIANTE, $programa);
        $joven = $this->paciente('2004-01-10', Perfil::ESTUDIANTE, $programa);
        $mayor = $this->paciente('1960-01-01', Perfil::OBRERO);
        $sinFecha = $this->paciente(null, Perfil::DOCENTE);

        $this->consulta($menor, $medico, $consultorio, $admin->id_use, '2024-06-15');
        $this->consulta($menor, $medico, $consultorio, $admin->id_use, '2024-06-16');
        $this->consulta($joven, $medico, $consultorio, $admin->id_use, '2024-06-10');
        $this->consulta($mayor, $medico, $consultorio, $admin->id_use, '2024-06-20');
        $this->consulta($sinFecha, $medico, $consultorio, $admin->id_use, '2024-06-21');
        $this->consulta($joven, $medico, $consultorio, $admin->id_use, '2024-08-01');

        $resumen = $this->actingAs($admin)
            ->withSession(['master_key_validated' => true, 'ultima_actividad' => time(), 'sede_activa' => $sede])
            ->getJson(route('admin.salud.movimientos.consultas.estadisticas', [
                'format' => 'json', 'start_date' => '2024-06-01', 'end_date' => '2024-06-30', 'consultorio_id' => $consultorio,
            ]))
            ->assertOk()
            ->json('resumen');

        $this->assertSame(5, $resumen['total_consultas']);
        $this->assertSame([
            'Menos de 18' => 1,
            '18 a 24' => 2,
            '25 a 34' => 0,
            '35 a 44' => 0,
            '45 a 59' => 0,
            '60 o más' => 1,
            SaludHomeService::SIN_FECHA_NACIMIENTO => 1,
        ], $resumen['edad_consulta']);

        $this->assertSame(3, $resumen['pnf_consulta']['PNF DE PRUEBA ESTADISTICAS']);
        $this->assertSame(2, $resumen['pnf_consulta'][SaludHomeService::SIN_PNF]);

        $this->assertSame(3, $resumen['tipo_persona_consulta'][Perfil::ESTUDIANTE]);
        $this->assertSame(1, $resumen['tipo_persona_consulta'][Perfil::OBRERO]);
        $this->assertSame(1, $resumen['tipo_persona_consulta'][Perfil::DOCENTE]);
    }

    public function test_edad_se_calcula_a_la_fecha_de_la_consulta(): void
    {
        $this->assertSame(17, SaludHomeService::edadAlConsultar('2006-06-16', '2024-06-15'));
        $this->assertSame(18, SaludHomeService::edadAlConsultar('2006-06-16', '2024-06-16'));
        $this->assertNull(SaludHomeService::edadAlConsultar(null, '2024-06-16'));
        $this->assertSame('60 o más', SaludHomeService::rangoEdad(64));
        $this->assertSame('45 a 59', SaludHomeService::rangoEdad(59));
    }
}
