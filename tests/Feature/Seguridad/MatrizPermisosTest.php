<?php

namespace Tests\Feature\Seguridad;

use App\Models\Rol;
use Illuminate\Support\Facades\DB;

class MatrizPermisosTest extends SeguridadTestCase
{
    private function comoAdministrador()
    {
        $admin = $this->crearUsuario('Administrador');

        return $this->actingAs($admin)->withSession(['master_key_validated' => true, 'ultima_actividad' => time()]);
    }

    public function test_el_seed_deja_vistas_y_permisos(): void
    {
        $this->assertGreaterThan(0, DB::table('vista')->whereNull('deleted_at')->count());
        $this->assertGreaterThan(0, DB::table('permiso')->whereNull('deleted_at')->count());
        $this->assertSame(0, DB::table('permiso')->whereNull('vis_id')->count());
    }

    public function test_crear_rol_muestra_checkboxes_de_permisos(): void
    {
        $this->comoAdministrador()
            ->get(route('admin.configuracion.roles.create'))
            ->assertOk()
            ->assertSee('name="permisos[]"', false)
            ->assertSee('class="perm-cell', false);
    }

    public function test_editar_rol_muestra_checkboxes_marcados(): void
    {
        $rol = Rol::where('nombre_rol', 'Paciente')->firstOrFail();
        $permiso = $rol->permisos()->first();
        $this->assertNotNull($permiso);

        $html = $this->comoAdministrador()
            ->get(route('admin.configuracion.roles.edit', $rol->id_rol))
            ->assertOk()
            ->assertSee('name="permisos[]"', false)
            ->getContent();

        $this->assertMatchesRegularExpression('/value="' . $permiso->id_pmi . '"[^>]*checked/s', $html);
    }

    public function test_editar_permisos_de_usuario_muestra_checkboxes(): void
    {
        $usuario = $this->crearUsuario('Paciente');

        $this->comoAdministrador()
            ->get(route('admin.configuracion.permisos.edit', $usuario->id_use))
            ->assertOk()
            ->assertSee('class="perm-cell', false);
    }

    public function test_guardar_un_rol_persiste_sus_permisos(): void
    {
        $permisos = DB::table('permiso')->whereNull('deleted_at')->orderBy('id_pmi')->limit(3)->pluck('id_pmi')->all();

        $this->comoAdministrador()
            ->post(route('admin.configuracion.roles.store'), [
                'nombre'      => 'Rol de prueba matriz',
                'descripcion' => 'Prueba',
                'permisos'    => $permisos,
            ])
            ->assertSessionHasNoErrors();

        $rol = Rol::where('nombre_rol', 'Rol de prueba matriz')->firstOrFail();
        $this->assertEqualsCanonicalizing($permisos, $rol->permisos()->pluck('permiso.id_pmi')->all());
    }
}
