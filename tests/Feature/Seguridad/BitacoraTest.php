<?php

namespace Tests\Feature\Seguridad;

use App\Models\Bitacora as BitacoraModelo;
use App\Support\Auditoria\AgenteUsuario;
use App\Support\Auditoria\Bitacora;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use LogicException;

class BitacoraTest extends SeguridadTestCase
{
    public function test_update_y_delete_directos_sobre_bitacora_fallan_por_trigger(): void
    {
        $registro = Bitacora::registrar(accion: 'prueba_inmutabilidad', modulo: 'pruebas');
        $this->assertNotNull($registro);

        try {
            DB::table('bitacora_sistema')->where('id_bis', $registro->id_bis)->update(['tipo_evento_bis' => 'alterada']);
            $this->fail('El UPDATE sobre bitacora debió fallar.');
        } catch (QueryException $e) {
            $this->assertStringContainsString('solo insercion', $e->getMessage());
        }

        try {
            DB::table('bitacora_sistema')->where('id_bis', $registro->id_bis)->delete();
            $this->fail('El DELETE sobre bitacora debió fallar.');
        } catch (QueryException $e) {
            $this->assertStringContainsString('solo insercion', $e->getMessage());
        }

        $this->assertSame('prueba_inmutabilidad', DB::table('bitacora_sistema')->where('id_bis', $registro->id_bis)->value('tipo_evento_bis'));
    }

    public function test_el_modelo_bitacora_no_permite_modificar_ni_eliminar(): void
    {
        $registro = Bitacora::registrar(accion: 'prueba_modelo', modulo: 'pruebas');

        $registro->tipo_evento_bis = 'alterada';
        try {
            $registro->save();
            $this->fail('save() sobre un registro existente debió fallar.');
        } catch (LogicException $e) {
            $this->assertStringContainsString('solo inserción', $e->getMessage());
        }

        $this->expectException(LogicException::class);
        $registro->delete();
    }

    public function test_cambios_de_modelo_se_registran_sin_campos_sensibles(): void
    {
        $usuario = $this->crearUsuario();

        $creacion = BitacoraModelo::where('tabla_afectada_bis', 'user')
            ->where('registro_id_bis', $usuario->id_use)
            ->where('tipo_evento_bis', 'crear')
            ->first();

        $this->assertNotNull($creacion);
        $this->assertArrayHasKey('username_use', $creacion->datos_nuevos_bis);
        $this->assertArrayNotHasKey('password_use', $creacion->datos_nuevos_bis);
        $this->assertArrayNotHasKey('master_key_use', $creacion->datos_nuevos_bis);

        $usuario->password_use = 'OtraClave123';
        $usuario->username_use = 'renombrado' . $usuario->id_use . '@prueba.test';
        $usuario->save();

        $cambio = BitacoraModelo::where('tabla_afectada_bis', 'user')
            ->where('registro_id_bis', $usuario->id_use)
            ->where('tipo_evento_bis', 'actualizar')
            ->latest('id_bis')
            ->first();

        $this->assertNotNull($cambio);
        $this->assertSame($usuario->username_use, $cambio->datos_nuevos_bis['username_use']);
        $this->assertArrayHasKey('username_use', $cambio->datos_anteriores_bis);
        $this->assertArrayNotHasKey('password_use', $cambio->datos_nuevos_bis);
        $this->assertArrayNotHasKey('password_use', $cambio->datos_anteriores_bis);
        $this->assertStringContainsString('password', $cambio->observacion_bis);
        $this->assertStringNotContainsString('OtraClave123', json_encode($cambio->getAttributes()));
    }

    public function test_peticion_autenticada_se_registra_y_el_polling_no(): void
    {
        $usuario = $this->crearUsuario('Administrador');

        $this->actingAs($usuario)->get('/home');
        $this->actingAs($usuario)->getJson('/mensajes/contactos/lista');

        $this->assertTrue(BitacoraModelo::where('use_id', $usuario->id_use)->where('ruta_bis', 'home')->where('metodo_http_bis', 'GET')->exists());
        $this->assertFalse(BitacoraModelo::where('use_id', $usuario->id_use)->whereIn('ruta_bis', ['chat.contactos', 'chat.contacts'])->exists());
    }

    public function test_respuesta_403_se_registra_como_acceso_denegado(): void
    {
        $usuario = $this->crearUsuario('Paciente');

        $this->actingAs($usuario)->get('/admin/configuracion/roles')->assertForbidden();

        $evento = BitacoraModelo::where('use_id', $usuario->id_use)->where('tipo_evento_bis', 'acceso_denegado')->first();
        $this->assertNotNull($evento);
        $this->assertSame(403, $evento->codigo_respuesta_bis);
        $this->assertStringContainsString('roles', $evento->observacion_bis);
        $this->assertStringContainsString('ver', $evento->observacion_bis);
    }

    public function test_el_agente_de_usuario_se_interpreta(): void
    {
        $chrome = AgenteUsuario::analizar('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.6367.60 Safari/537.36');
        $this->assertSame('Chrome 124.0', $chrome['navegador']);
        $this->assertSame('Windows 10/11', $chrome['sistema_operativo']);

        $android = AgenteUsuario::analizar('Mozilla/5.0 (Linux; Android 14; SM-S918B) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/24.0 Chrome/117.0.0.0 Mobile Safari/537.36');
        $this->assertSame('Samsung Internet 24.0', $android['navegador']);
        $this->assertSame('Android 14', $android['sistema_operativo']);

        $firefox = AgenteUsuario::analizar('Mozilla/5.0 (X11; Linux x86_64; rv:131.0) Gecko/20100101 Firefox/131.0');
        $this->assertSame('Firefox 131.0', $firefox['navegador']);
        $this->assertSame('Linux', $firefox['sistema_operativo']);
    }
}
