<?php

namespace Tests\Feature\Seguridad;

use App\Models\Rol;
use App\Models\Usuario;
use App\Support\Permisos\VersionPermisos;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BotonesPorPermisoTest extends SeguridadTestCase
{
    private function usuarioConAcciones(array $acciones): Usuario
    {
        $vista = DB::table('vista')->where('key_vis', 'proveedores')->value('id_vis');
        $permisos = DB::table('permiso')->where('vis_id', $vista)->whereIn('accion_pmi', $acciones)->pluck('id_pmi');
        $modulo = DB::table('vista')->where('id_vis', $vista)->value('mod_id');

        $rol = Rol::create(['nombre_rol' => 'Proveedores ' . Str::random(6), 'slug_rol' => 'proveedores-' . Str::lower(Str::random(6))]);
        $rol->permisos()->sync($permisos);
        $rol->modulos()->sync([$modulo]);
        VersionPermisos::invalidar();

        $usuario = $this->crearUsuario();
        $usuario->roles()->sync([$rol->id_rol]);

        return $usuario->fresh();
    }

    private function proveedor(): int
    {
        return DB::table('proveedor')->insertGetId([
            'empresa_prv' => 'PROVEEDOR BOTONES', 'telefono_prv' => '0424', 'email_prv' => 'botones' . random_int(1, 99999) . '@x.test',
            'created_at' => now(), 'updated_at' => now(),
        ], 'id_prv');
    }

    public function test_solo_ver_no_muestra_botones_de_accion(): void
    {
        $id = $this->proveedor();
        $usuario = $this->usuarioConAcciones(['ver']);

        $html = $this->actingAs($usuario)->get(route('admin.maestros.proveedores.index', ['buscar' => 'PROVEEDOR BOTONES']))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString(route('admin.maestros.proveedores.create'), $html);
        $this->assertStringNotContainsString('maestros/proveedores/' . $id . '/edit', $html);
        $this->assertStringNotContainsString('form-toggle-' . $id, $html);
        $this->assertStringContainsString('title="Solo lectura"', $html);
    }

    public function test_con_todas_las_acciones_muestra_todos_los_botones(): void
    {
        $id = $this->proveedor();
        $usuario = $this->usuarioConAcciones(['ver', 'crear', 'editar', 'eliminar', 'activar', 'exportar']);

        $html = $this->actingAs($usuario)->get(route('admin.maestros.proveedores.index', ['buscar' => 'PROVEEDOR BOTONES']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(route('admin.maestros.proveedores.create'), $html);
        $this->assertStringContainsString('maestros/proveedores/' . $id . '/edit', $html);
        $this->assertStringContainsString('form-toggle-' . $id, $html);
        $this->assertStringNotContainsString('title="Solo lectura"', $html);
    }
}
