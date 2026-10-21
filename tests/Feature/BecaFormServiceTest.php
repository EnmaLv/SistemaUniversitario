<?php

namespace Tests\Feature;

use App\Models\Becas\Beca;
use App\Models\Persona;
use App\Models\Usuario;
use App\Services\BecaFormService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class BecaFormServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_tutor_list_contains_only_people_with_roles()
    {
        $this->markTestIncomplete('Prueba previa a la rama de seguridad: inserta perfil.id_estatus y sucursals, que no existen en el esquema actual. Reescribir con las tablas actuales.');

        $estatusId = DB::table('estatus')->insertGetId([
            'nombre_estatus' => 'Activo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $perfilId = DB::table('perfil')->insertGetId([
            'nombre_perfil' => 'Estudiante',
            'id_estatus' => $estatusId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sucursalId = DB::table('sucursals')->insertGetId([
            'nombre' => 'Sucursal Test',
            'direccion' => 'Dirección Test',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sedeId = DB::table('sede')->insertGetId([
            'nombre_sede' => 'Sede Test',
            'estatus' => 1,
            'id_sucursal' => $sucursalId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tutorPerfilId = DB::table('perfil')->insertGetId([
            'nombre_perfil' => 'Tutor',
            'id_estatus' => $estatusId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $student = Persona::create([
            'nombre_persona' => 'Estudiante',
            'segundo_nombre_persona' => null,
            'apellido_persona' => 'Prueba',
            'segundo_apellido_persona' => null,
            'cedula_persona' => '11111111',
            'telefono_persona' => '04141234567',
            'genero_persona' => 'M',
            'edad_persona' => 20,
            'fecha_nacimiento_persona' => '2004-01-01',
            'email_persona' => 'estudiante@test.com',
            'semestre_persona' => '1',
            'estado' => true,
            'id_perfil' => $perfilId,
            'id_sede' => $sedeId,
        ]);

        $tutor = Persona::create([
            'nombre_persona' => 'Tutor',
            'segundo_nombre_persona' => null,
            'apellido_persona' => 'Prueba',
            'segundo_apellido_persona' => null,
            'cedula_persona' => '22222222',
            'telefono_persona' => '04141234568',
            'genero_persona' => 'F',
            'edad_persona' => 30,
            'fecha_nacimiento_persona' => '1994-01-01',
            'email_persona' => 'tutor@test.com',
            'semestre_persona' => null,
            'estado' => true,
            'id_perfil' => $tutorPerfilId,
            'id_sede' => $sedeId,
        ]);

        $roleId = DB::table('rol')->insertGetId([
            'nombre' => 'Tutor',
            'slug' => 'tutor',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = Usuario::create([
            'id_persona' => $tutor->id_persona,
            'id_perfil' => 1,
            'username' => 'tutoruser',
            'password' => 'password123',
        ]);

        DB::table('rol_usuario')->insert([
            'id_rol' => $roleId,
            'id_usuario' => $user->id_usuario,
        ]);

        $formService = new BecaFormService();
        $datos = $formService->datosFormulario();

        $tutorIds = $datos['tutores']->pluck('id_persona')->all();

        $this->assertContains($tutor->id_persona, $tutorIds);
        $this->assertNotContains($student->id_persona, $tutorIds);

        $estudianteIds = $datos['estudiantes']->pluck('id_persona')->all();

        $this->assertContains($student->id_persona, $estudianteIds);
        $this->assertNotContains($tutor->id_persona, $estudianteIds);
    }

    public function test_tutor_list_includes_personas_with_roles_even_if_persona_is_inactive()
    {
        $this->markTestIncomplete('Prueba previa a la rama de seguridad: inserta perfil.id_estatus y sucursals, que no existen en el esquema actual. Reescribir con las tablas actuales.');

        $estatusId = DB::table('estatus')->insertGetId([
            'nombre_estatus' => 'Activo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $perfilId = DB::table('perfil')->insertGetId([
            'nombre_perfil' => 'Tutor',
            'id_estatus' => $estatusId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sucursalId = DB::table('sucursals')->insertGetId([
            'nombre' => 'Sucursal Test',
            'direccion' => 'Dirección Test',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sedeId = DB::table('sede')->insertGetId([
            'nombre_sede' => 'Sede Test',
            'estatus' => 1,
            'id_sucursal' => $sucursalId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $persona = Persona::create([
            'nombre_persona' => 'Encargada',
            'segundo_nombre_persona' => null,
            'apellido_persona' => 'Becas',
            'segundo_apellido_persona' => null,
            'cedula_persona' => '33333333',
            'telefono_persona' => '04141234569',
            'genero_persona' => 'F',
            'edad_persona' => 35,
            'fecha_nacimiento_persona' => '1990-01-01',
            'email_persona' => 'becas@test.com',
            'semestre_persona' => null,
            'estado' => false,
            'id_perfil' => $perfilId,
            'id_sede' => $sedeId,
        ]);

        $roleId = DB::table('rol')->insertGetId([
            'nombre' => 'Encargada de Becas',
            'slug' => 'encargada-de-becas',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = Usuario::create([
            'id_persona' => $persona->id_persona,
            'id_perfil' => 1,
            'username' => 'becasuser',
            'password' => 'password123',
        ]);

        DB::table('rol_usuario')->insert([
            'id_rol' => $roleId,
            'id_usuario' => $user->id_usuario,
        ]);

        $datos = (new BecaFormService())->datosFormulario();

        $this->assertContains($persona->id_persona, $datos['tutores']->pluck('id_persona')->all());
    }

    public function test_edit_form_defaults_to_no_when_beca_has_no_tutores()
    {
        $this->markTestIncomplete('Prueba previa a la rama de seguridad: usa App\\Models\\Becas\\Beca, que no existe en el proyecto. Reescribir con los modelos actuales.');

        $beca = Beca::create([
            'codigo' => 'TEST-001',
            'nombre' => 'Beca de prueba',
            'descripcion' => 'Descripción prueba',
            'activo' => true,
            'requiere_tutor' => false,
        ]);

        $view = view('admin.becas._form_fields', [
            'beca' => $beca,
            'beneficios' => collect(),
            'tutores' => collect(),
            'estudiantes' => collect(),
            'errors' => new ViewErrorBag(),
        ])->render();

        $this->assertStringContainsString('id="requiere_tutor_no"', $view);
        $this->assertStringContainsString('value="0"', $view);
        $this->assertStringContainsString('checked', $view);
        $this->assertStringContainsString('id="requiere_tutor_si"', $view);
        $this->assertStringNotContainsString('id="requiere_tutor_si"' . ' checked', $view);
    }
}
