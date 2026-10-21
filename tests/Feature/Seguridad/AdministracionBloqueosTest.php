<?php

namespace Tests\Feature\Seguridad;

use App\Models\Bitacora as BitacoraModelo;
use App\Support\Seguridad\Bloqueos;

class AdministracionBloqueosTest extends SeguridadTestCase
{
    public function test_el_administrador_ve_y_desbloquea_usuarios(): void
    {
        $admin = $this->crearUsuario('Administrador');
        $bloqueado = $this->crearUsuario('Obrero');
        Bloqueos::aplicarBloqueoPermanente($bloqueado, Bloqueos::MOTIVO_TEMPORALES);

        $this->actingAs($admin)
            ->withSession(['master_key_validated' => true])
            ->get('/admin/configuracion/seguridad/bloqueos')
            ->assertOk()
            ->assertSee($bloqueado->username_use)
            ->assertSee('Bloqueos temporales repetidos');

        $this->put("/admin/configuracion/seguridad/bloqueos/{$bloqueado->id_use}/desbloquear")
            ->assertRedirect(route('admin.configuracion.seguridad.bloqueos.index'));

        $bloqueado->refresh();
        $this->assertFalse($bloqueado->bloqueado_use);
        $this->assertSame(0, $bloqueado->bloqueos_temporales_use);

        $evento = BitacoraModelo::where('accion', 'desbloqueo')->where('registro_id', $bloqueado->id_use)->first();
        $this->assertNotNull($evento);
        $this->assertSame($admin->id_use, $evento->usuario_id);
    }

    public function test_un_usuario_no_puede_desbloquearse_a_si_mismo(): void
    {
        $admin = $this->crearUsuario('Administrador');
        $admin->forceFill(['intentos_fallidos_use' => 2, 'bloqueos_temporales_use' => 1])->saveQuietly();

        $this->actingAs($admin)
            ->withSession(['master_key_validated' => true])
            ->put("/admin/configuracion/seguridad/bloqueos/{$admin->id_use}/desbloquear")
            ->assertSessionHas('error');

        $this->assertSame(1, $admin->fresh()->bloqueos_temporales_use);
        $this->assertFalse(BitacoraModelo::where('accion', 'desbloqueo')->where('registro_id', $admin->id_use)->exists());
    }

    public function test_usuario_sin_permiso_no_accede_a_la_pantalla_de_bloqueos(): void
    {
        $usuario = $this->crearUsuario('Obrero');

        $this->actingAs($usuario)->get('/admin/configuracion/seguridad/bloqueos')->assertForbidden();
        $this->actingAs($usuario)->get('/admin/configuracion/bitacora')->assertForbidden();
    }

    public function test_la_bitacora_es_visible_para_el_administrador(): void
    {
        $admin = $this->crearUsuario('Administrador');

        $this->actingAs($admin)
            ->withSession(['master_key_validated' => true])
            ->get('/admin/configuracion/bitacora?nivel=info')
            ->assertOk()
            ->assertSee('Bitácora de auditoría');

        $evento = BitacoraModelo::latest('id')->first();
        $this->get("/admin/configuracion/bitacora/{$evento->id}")->assertOk()->assertSee('antes → después', false);

        $this->get('/admin/configuracion/bitacora/exportar/csv')->assertOk();
        $this->assertTrue(BitacoraModelo::where('accion', 'bitacora_exportada')->where('usuario_id', $admin->id_use)->exists());
    }

    public function test_comando_de_consola_desbloquea_y_registra_sin_usuario(): void
    {
        $usuario = $this->crearUsuario('Administrador');
        Bloqueos::aplicarBloqueoPermanente($usuario, Bloqueos::MOTIVO_ACCESOS);

        $this->artisan('seguridad:desbloquear', ['username' => $usuario->username_use])->assertSuccessful();

        $this->assertFalse($usuario->fresh()->bloqueado_use);
        $evento = BitacoraModelo::where('accion', 'desbloqueo')->where('registro_id', $usuario->id_use)->latest('id')->first();
        $this->assertNotNull($evento);
        $this->assertNull($evento->usuario_id);
        $this->assertStringStartsWith('consola', $evento->observacion);
    }
}
