<?php

namespace Tests\Feature\Seguridad;

use App\Models\Bitacora as BitacoraModelo;
use App\Support\Permisos\VersionPermisos;
use App\Support\Seguridad\Bloqueos;
use Illuminate\Support\Facades\DB;

class AccesosDenegadosTest extends SeguridadTestCase
{
    public function test_la_vista_403_avisa_los_intentos_restantes(): void
    {
        $usuario = $this->crearUsuario('Obrero');

        $this->actingAs($usuario)
            ->get('/admin/configuracion/roles')
            ->assertForbidden()
            ->assertSee('Página restringida')
            ->assertSee('Te quedan')
            ->assertSee('9');
    }

    public function test_diez_accesos_denegados_bloquean_y_desconectan_al_usuario(): void
    {
        $usuario = $this->crearUsuario('Obrero');
        $this->actingAs($usuario);

        for ($i = 1; $i <= 9; $i++) {
            $this->get('/admin/configuracion/roles')->assertForbidden();
        }

        $this->assertAuthenticated();

        $this->get('/admin/configuracion/roles')->assertRedirect(route('login'));
        $this->assertGuest();

        $usuario->refresh();
        $this->assertTrue($usuario->bloqueado);
        $this->assertSame(Bloqueos::MOTIVO_ACCESOS, $usuario->motivo_bloqueo);
        $this->assertSame(10, BitacoraModelo::where('usuario_id', $usuario->id_usuario)->where('accion', 'acceso_denegado')->count());
        $this->assertTrue(BitacoraModelo::where('usuario_id', $usuario->id_usuario)->where('accion', 'bloqueo_permanente')->where('nivel', 'critico')->exists());
    }

    public function test_los_accesos_fuera_de_la_ventana_no_se_acumulan(): void
    {
        $usuario = $this->crearUsuario('Obrero');
        $this->actingAs($usuario);

        for ($i = 1; $i <= 9; $i++) {
            $this->get('/admin/configuracion/roles')->assertForbidden();
        }

        $this->travel(11)->minutes();

        $this->get('/admin/configuracion/roles')->assertForbidden();
        $this->assertAuthenticated();
        $this->assertFalse((bool) $usuario->fresh()->bloqueado);
    }

    public function test_un_superadmin_tambien_suma_403_que_no_vienen_de_check_menu_permission(): void
    {
        $admin = $this->crearUsuario('Administrador');

        $this->actingAs($admin)->get(route('admin.psicologia.maestros.historias.index'))->assertForbidden();

        $this->assertTrue(BitacoraModelo::where('usuario_id', $admin->id_usuario)->where('accion', 'acceso_denegado')->exists());
    }

    public function test_sin_permiso_editar_recibe_403_en_update_y_con_permiso_pasa(): void
    {
        $usuario = $this->crearUsuario('Obrero');
        $sedeId = DB::table('sede')->value('id');
        $datos = ['nombre' => 'Sede editada', 'direccion' => 'Dirección', 'telefono' => '0424-1234567'];

        $this->actingAs($usuario)->put("/admin/maestros/sedes/{$sedeId}", $datos)->assertForbidden();

        DB::table('usuario_permiso')->insert([
            'usuario_id' => $usuario->id_usuario,
            'permiso_id' => DB::table('permiso')->where('key', 'sedes.editar')->value('id'),
            'tipo'       => 'grant',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        VersionPermisos::invalidar();

        $respuesta = $this->actingAs($usuario->fresh())->put("/admin/maestros/sedes/{$sedeId}", $datos);
        $this->assertNotSame(403, $respuesta->getStatusCode());
    }

    public function test_path_traversal_en_archivos_devuelve_404(): void
    {
        $admin = $this->crearUsuario('Administrador');
        $this->actingAs($admin);

        $this->get('/admin/configuracion/archivos/ver/..%2F..%2F.env')->assertNotFound();
        $this->get('/admin/configuracion/archivos/ver/' . rawurlencode('../../../.env'))->assertNotFound();
        $this->get('/admin/media/profile-photos/..%2F..%2F..%2F.env')->assertNotFound();
    }
}
