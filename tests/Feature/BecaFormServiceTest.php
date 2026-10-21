<?php

namespace Tests\Feature;

use App\Models\Becas\Beca;
use App\Models\Persona;
use App\Models\Usuario;
use App\Services\BecaFormService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class BecaFormServiceTest extends TestCase
{
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
        ], 'id_perfil');

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
        ], 'id_sed');

        $tutorPerfilId = DB::table('perfil')->insertGetId([
            'nombre_perfil' => 'Tutor',
            'id_estatus' => $estatusId,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_perfil');

        $student = Persona::create([
            'nombres_per' => 'Estudiante',
            'segundo_nombre_per' => null,
            'apellidos_per' => 'Prueba',
            'segundo_apellido_per' => null,
            'cedula_per' => '11111111',
            'telefono_per' => '04141234567',
            'genero_per' => 'M',
            'edad_persona' => 20,
            'fecha_nacimiento_per' => '2004-01-01',
            'correo_per' => 'estudiante@test.com',
            'semestre_per' => '1',
            'estado' => true,
            'id_perfil' => $perfilId,
            'sed_id' => $sedeId,
        ]);

        $tutor = Persona::create([
            'nombres_per' => 'Tutor',
            'segundo_nombre_per' => null,
            'apellidos_per' => 'Prueba',
            'segundo_apellido_per' => null,
            'cedula_per' => '22222222',
            'telefono_per' => '04141234568',
            'genero_per' => 'F',
            'edad_persona' => 30,
            'fecha_nacimiento_per' => '1994-01-01',
            'correo_per' => 'tutor@test.com',
            'semestre_per' => null,
            'estado' => true,
            'id_perfil' => $tutorPerfilId,
            'sed_id' => $sedeId,
        ]);

        $roleId = DB::table('rol')->insertGetId([
            'nombre' => 'Tutor',
            'slug' => 'tutor',
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_rol');

        $user = Usuario::create([
            'per_id' => $tutor->per_id,
            'id_perfil' => 1,
            'username' => 'tutoruser',
            'password' => 'password123',
        ]);

        DB::table('usuario_rol')->insert([
            'id_rol' => $roleId,
            'id_use' => $user->id_use,
        ]);

        $formService = new BecaFormService();
        $datos = $formService->datosFormulario();

        $tutorIds = $datos['tutores']->pluck('per_id')->all();

        $this->assertContains($tutor->per_id, $tutorIds);
        $this->assertNotContains($student->per_id, $tutorIds);

        $estudianteIds = $datos['estudiantes']->pluck('per_id')->all();

        $this->assertContains($student->per_id, $estudianteIds);
        $this->assertNotContains($tutor->per_id, $estudianteIds);
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
        ], 'id_perfil');

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
        ], 'id_sed');

        $persona = Persona::create([
            'nombres_per' => 'Encargada',
            'segundo_nombre_per' => null,
            'apellidos_per' => 'Becas',
            'segundo_apellido_per' => null,
            'cedula_per' => '33333333',
            'telefono_per' => '04141234569',
            'genero_per' => 'F',
            'edad_persona' => 35,
            'fecha_nacimiento_per' => '1990-01-01',
            'correo_per' => 'becas@test.com',
            'semestre_per' => null,
            'estado' => false,
            'id_perfil' => $perfilId,
            'sed_id' => $sedeId,
        ]);

        $roleId = DB::table('rol')->insertGetId([
            'nombre' => 'Encargada de Becas',
            'slug' => 'encargada-de-becas',
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_rol');

        $user = Usuario::create([
            'per_id' => $persona->id_per,
            'id_perfil' => 1,
            'username' => 'becasuser',
            'password' => 'password123',
        ]);

        DB::table('usuario_rol')->insert([
            'id_rol' => $roleId,
            'id_use' => $user->id_use,
        ]);

        $datos = (new BecaFormService())->datosFormulario();

        $this->assertContains($persona->id_per, $datos['tutores']->pluck('per_id')->all());
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
