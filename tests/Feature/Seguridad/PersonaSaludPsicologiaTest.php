<?php

namespace Tests\Feature\Seguridad;

use App\Livewire\RegistroPersona;
use App\Models\Perfil;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

class PersonaSaludPsicologiaTest extends SeguridadTestCase
{
    private function asegurarModulosYRoles(): void
    {
        $modSalud = DB::table('modulo')->where('key_mod', 'salud')->value('id_mod');
        $modPsico = DB::table('modulo')->where('key_mod', 'psicologia')->value('id_mod');

        $rolDoctor = Rol::where('nombre_rol', 'Doctor(a)')->first();
        if ($rolDoctor && $modSalud) {
            DB::table('rol_modulo')->updateOrInsert(['rol_id' => $rolDoctor->id_rol, 'mod_id' => $modSalud]);
        }

        $rolPsico = Rol::where('nombre_rol', 'Psicólogo')->first();
        if ($rolPsico && $modPsico) {
            DB::table('rol_modulo')->updateOrInsert(['rol_id' => $rolPsico->id_rol, 'mod_id' => $modPsico]);
        }
    }

    private function prepararDatosDireccion(): array
    {
        $estado = DB::table('estado')->first() ?? (object) ['id_esd' => DB::table('estado')->insertGetId([
            'nombre_esd' => 'Estado Prueba',
            'status_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_esd')];

        $municipio = DB::table('municipio')->where('esd_id', $estado->id_esd)->first() ?? (object) ['id_mun' => DB::table('municipio')->insertGetId([
            'nombre_mun' => 'Municipio Prueba',
            'esd_id' => $estado->id_esd,
            'status_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_mun')];

        $parroquia = DB::table('parroquia')->where('mun_id', $municipio->id_mun)->first() ?? (object) ['id_par' => DB::table('parroquia')->insertGetId([
            'nombre_par' => 'Parroquia Prueba',
            'mun_id' => $municipio->id_mun,
            'status_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_par')];

        return [
            'estadosVeId'  => (int) $estado->id_esd,
            'municipiosId' => (int) $municipio->id_mun,
            'parroquiaId'  => (int) $parroquia->id_par,
            'calle'        => 'Calle Principal',
            'sector'       => 'Sector Central',
        ];
    }

    public function test_doctor_puede_acceder_a_persona_index_y_create_con_modulo_salud(): void
    {
        $this->asegurarModulosYRoles();
        $doctor = $this->crearUsuario('Doctor(a)');

        $this->actingAs($doctor)
            ->withSession([
                'modulo_activo'      => 'salud',
                'modulos_permitidos' => ['salud'],
                'sede_activa'        => $this->sedeA(),
            ]);

        $this->get(route('admin.configuracion.persona.index'))
            ->assertOk()
            ->assertSee('Personas')
            ->assertSee('Registrar persona')
            ->assertSee('Comunidad universitaria');

        $this->get(route('admin.configuracion.persona.create'))
            ->assertOk()
            ->assertSee('Registrar persona');
    }

    public function test_psicologo_puede_acceder_a_persona_index_y_create_con_modulo_psicologia(): void
    {
        $this->asegurarModulosYRoles();
        $psicologo = $this->crearUsuario('Psicólogo');

        $this->actingAs($psicologo)
            ->withSession([
                'modulo_activo'      => 'psicologia',
                'modulos_permitidos' => ['psicologia'],
                'sede_activa'        => $this->sedeA(),
            ]);

        $this->get(route('admin.configuracion.persona.index'))
            ->assertOk()
            ->assertSee('Personas')
            ->assertSee('Registrar persona');

        $this->get(route('admin.configuracion.persona.create'))
            ->assertOk();
    }

    public function test_listado_en_salud_solo_muestra_comunidad_y_obreros(): void
    {
        $this->asegurarModulosYRoles();
        $doctor = $this->crearUsuario('Doctor(a)');
        $sede = $this->sedeA();

        $estudiante = Persona::create([
            'nombres_per'   => 'JUAN ESTUDIANTE',
            'apellidos_per' => 'PEREZ',
            'cedula_per'    => '31111111',
            'telefono_per'  => '04141111111',
            'genero_per'    => 'MASCULINO',
            'correo_per'    => 'estudiante@prueba.test',
            'id_perfil'     => Perfil::idEstudiante(),
            'sed_id'        => $sede,
            'status_id'     => 1,
        ]);

        $docente = Persona::create([
            'nombres_per'   => 'MARIA DOCENTE',
            'apellidos_per' => 'GARCIA',
            'cedula_per'    => '32222222',
            'telefono_per'  => '04142222222',
            'genero_per'    => 'FEMENINO',
            'correo_per'    => 'docente@prueba.test',
            'id_perfil'     => Perfil::idPorNombre(Perfil::DOCENTE),
            'sed_id'        => $sede,
            'status_id'     => 1,
        ]);

        $comunidad = Persona::create([
            'nombres_per'   => 'CARLOS COMUNIDAD',
            'apellidos_per' => 'LOPEZ',
            'cedula_per'    => '33333333',
            'telefono_per'  => '04143333333',
            'genero_per'    => 'MASCULINO',
            'correo_per'    => 'comunidad@prueba.test',
            'id_perfil'     => Perfil::idPorNombre(Perfil::COMUNIDAD),
            'sed_id'        => $sede,
            'status_id'     => 1,
        ]);

        $obrero = Persona::create([
            'nombres_per'   => 'PEDRO OBRERO',
            'apellidos_per' => 'MARTINEZ',
            'cedula_per'    => '34444444',
            'telefono_per'  => '04144444444',
            'genero_per'    => 'MASCULINO',
            'correo_per'    => 'obrero@prueba.test',
            'id_perfil'     => Perfil::idPorNombre(Perfil::OBRERO),
            'sed_id'        => $sede,
            'status_id'     => 1,
        ]);

        $this->actingAs($doctor)
            ->withSession([
                'modulo_activo'      => 'salud',
                'modulos_permitidos' => ['salud'],
                'sede_activa'        => $sede,
            ]);

        $response = $this->get(route('admin.configuracion.persona.index'));
        $response->assertOk();

        $response->assertSee('33333333');
        $response->assertSee('CARLOS COMUNIDAD');
        $response->assertSee('34444444');
        $response->assertSee('PEDRO OBRERO');

        $response->assertDontSee('31111111');
        $response->assertDontSee('JUAN ESTUDIANTE');
        $response->assertDontSee('32222222');
        $response->assertDontSee('MARIA DOCENTE');
    }

    public function test_usuario_salud_no_puede_registrar_estudiante_docente_o_administrativo(): void
    {
        $this->asegurarModulosYRoles();
        $doctor = $this->crearUsuario('Doctor(a)');
        $dir = $this->prepararDatosDireccion();
        $sede = $this->sedeA();

        $this->actingAs($doctor)
            ->withSession([
                'modulo_activo'      => 'salud',
                'modulos_permitidos' => ['salud'],
                'sede_activa'        => $sede,
            ]);

        // 1. Prohibido Estudiante
        Livewire::test(RegistroPersona::class)
            ->set('cedula', '29999001')
            ->set('nombre', 'Pedro')
            ->set('apellido', 'Perez')
            ->set('fecha_nacimiento', '2000-01-01')
            ->set('genero', 'MASCULINO')
            ->set('telefono', '04141112233')
            ->set('email', 'pedro001@test.com')
            ->set('estadosVeId', $dir['estadosVeId'])
            ->set('municipiosId', $dir['municipiosId'])
            ->set('parroquiaId', $dir['parroquiaId'])
            ->set('calle', $dir['calle'])
            ->set('sector', $dir['sector'])
            ->set('sedeId', $sede)
            ->set('perfilId', Perfil::idEstudiante())
            ->call('create')
            ->assertHasErrors(['perfilId']);

        $this->assertDatabaseMissing('persona', ['cedula_per' => '29999001']);

        // 2. Prohibido Docente
        Livewire::test(RegistroPersona::class)
            ->set('cedula', '29999002')
            ->set('nombre', 'Ana')
            ->set('apellido', 'Docente')
            ->set('fecha_nacimiento', '1990-01-01')
            ->set('genero', 'FEMENINO')
            ->set('telefono', '04141112234')
            ->set('email', 'ana002@test.com')
            ->set('estadosVeId', $dir['estadosVeId'])
            ->set('municipiosId', $dir['municipiosId'])
            ->set('parroquiaId', $dir['parroquiaId'])
            ->set('calle', $dir['calle'])
            ->set('sector', $dir['sector'])
            ->set('sedeId', $sede)
            ->set('perfilId', Perfil::idPorNombre(Perfil::DOCENTE))
            ->call('create')
            ->assertHasErrors(['perfilId']);

        $this->assertDatabaseMissing('persona', ['cedula_per' => '29999002']);

        // 3. Prohibido Administrativo
        Livewire::test(RegistroPersona::class)
            ->set('cedula', '29999003')
            ->set('nombre', 'Luis')
            ->set('apellido', 'Admin')
            ->set('fecha_nacimiento', '1992-01-01')
            ->set('genero', 'MASCULINO')
            ->set('telefono', '04141112235')
            ->set('email', 'luis003@test.com')
            ->set('estadosVeId', $dir['estadosVeId'])
            ->set('municipiosId', $dir['municipiosId'])
            ->set('parroquiaId', $dir['parroquiaId'])
            ->set('calle', $dir['calle'])
            ->set('sector', $dir['sector'])
            ->set('sedeId', $sede)
            ->set('perfilId', Perfil::idPorNombre(Perfil::ADMINISTRATIVO))
            ->call('create')
            ->assertHasErrors(['perfilId']);

        $this->assertDatabaseMissing('persona', ['cedula_per' => '29999003']);
    }

    public function test_usuario_salud_puede_registrar_comunidad_u_obrero(): void
    {
        $this->asegurarModulosYRoles();
        $doctor = $this->crearUsuario('Doctor(a)');
        $dir = $this->prepararDatosDireccion();
        $sede = $this->sedeA();

        $this->actingAs($doctor)
            ->withSession([
                'modulo_activo'      => 'salud',
                'modulos_permitidos' => ['salud'],
                'sede_activa'        => $sede,
            ]);

        // Registro de Comunidad Universitaria
        Livewire::test(RegistroPersona::class)
            ->set('cedula', '29999004')
            ->set('nombre', 'Roberto')
            ->set('apellido', 'Comunidad')
            ->set('fecha_nacimiento', '2001-05-10')
            ->set('genero', 'MASCULINO')
            ->set('telefono', '04141112236')
            ->set('email', 'roberto004@test.com')
            ->set('estadosVeId', $dir['estadosVeId'])
            ->set('municipiosId', $dir['municipiosId'])
            ->set('parroquiaId', $dir['parroquiaId'])
            ->set('calle', $dir['calle'])
            ->set('sector', $dir['sector'])
            ->set('sedeId', $sede)
            ->set('perfilId', Perfil::idPorNombre(Perfil::COMUNIDAD))
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('persona', [
            'cedula_per' => '29999004',
            'id_perfil'  => Perfil::idPorNombre(Perfil::COMUNIDAD),
        ]);

        // Registro de Obrero
        Livewire::test(RegistroPersona::class)
            ->set('cedula', '29999005')
            ->set('nombre', 'Marcos')
            ->set('apellido', 'Obrero')
            ->set('fecha_nacimiento', '1985-03-15')
            ->set('genero', 'MASCULINO')
            ->set('telefono', '04141112237')
            ->set('email', 'marcos005@test.com')
            ->set('estadosVeId', $dir['estadosVeId'])
            ->set('municipiosId', $dir['municipiosId'])
            ->set('parroquiaId', $dir['parroquiaId'])
            ->set('calle', $dir['calle'])
            ->set('sector', $dir['sector'])
            ->set('sedeId', $sede)
            ->set('perfilId', Perfil::idPorNombre(Perfil::OBRERO))
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('persona', [
            'cedula_per' => '29999005',
            'id_perfil'  => Perfil::idPorNombre(Perfil::OBRERO),
        ]);
    }

    public function test_usuario_salud_recibe_403_al_intentar_ver_o_editar_estudiante(): void
    {
        $this->asegurarModulosYRoles();
        $doctor = $this->crearUsuario('Doctor(a)');
        $sede = $this->sedeA();

        $estudiante = Persona::create([
            'nombres_per'   => 'ESTUDIANTE RESTRINGIDO',
            'apellidos_per' => 'PRUEBA',
            'cedula_per'    => '35555555',
            'telefono_per'  => '04145555555',
            'genero_per'    => 'MASCULINO',
            'correo_per'    => 'restringido@prueba.test',
            'id_perfil'     => Perfil::idEstudiante(),
            'sed_id'        => $sede,
            'status_id'     => 1,
        ]);

        $this->actingAs($doctor)
            ->withSession([
                'modulo_activo'      => 'salud',
                'modulos_permitidos' => ['salud'],
                'sede_activa'        => $sede,
            ]);

        $this->get(route('admin.configuracion.persona.edit', ['id' => $estudiante->id_per]))
            ->assertForbidden();

        $this->get(route('admin.configuracion.persona.show', ['id' => $estudiante->id_per]))
            ->assertForbidden();
    }

    public function test_cedula_de_estudiante_existente_advierte_al_usuario_de_salud(): void
    {
        $this->asegurarModulosYRoles();
        $doctor = $this->crearUsuario('Doctor(a)');
        $sede = $this->sedeA();

        Persona::create([
            'nombres_per'   => 'ESTUDIANTE EXISTENTE',
            'apellidos_per' => 'PRUEBA',
            'cedula_per'    => '36666666',
            'telefono_per'  => '04146666666',
            'genero_per'    => 'MASCULINO',
            'correo_per'    => 'existente@prueba.test',
            'id_perfil'     => Perfil::idEstudiante(),
            'sed_id'        => $sede,
            'status_id'     => 1,
        ]);

        $this->actingAs($doctor)
            ->withSession([
                'modulo_activo'      => 'salud',
                'modulos_permitidos' => ['salud'],
                'sede_activa'        => $sede,
            ]);

        Livewire::test(RegistroPersona::class)
            ->set('cedula', '36666666')
            ->assertSet('formHabilitado', false)
            ->assertDispatched('alert', function ($event, $params) {
                return $params['title'] === 'Perfil restringido';
            });
    }
}

