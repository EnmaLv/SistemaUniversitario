<?php

namespace Tests\Feature\Seguridad;

use App\Models\Bitacora as BitacoraModelo;
use App\Support\Seguridad\Bloqueos;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class BloqueoCredencialesTest extends SeguridadTestCase
{
    private function intentarLogin(string $username, string $password)
    {
        return $this->from('/login')->post('/login', ['email' => $username, 'password' => $password]);
    }

    private function errorDe($respuesta): string
    {
        return (string) session('errors')?->first('email');
    }

    public function test_tres_contrasenas_incorrectas_aplican_bloqueo_temporal_que_luego_expira(): void
    {
        $usuario = $this->crearUsuario('Obrero');

        $this->assertSame(Bloqueos::MENSAJE_GENERICO, $this->errorDe($this->intentarLogin($usuario->username_use, 'mala-1')));
        $this->assertSame(Bloqueos::MENSAJE_GENERICO, $this->errorDe($this->intentarLogin($usuario->username_use, 'mala-2')));

        $tercera = $this->intentarLogin($usuario->username_use, 'mala-3');
        $this->assertStringContainsString('Demasiados intentos fallidos', $this->errorDe($tercera));
        $this->assertStringContainsString('5 minutos', $this->errorDe($tercera));

        $usuario->refresh();
        $this->assertTrue($usuario->bloqueado_hasta_use->isFuture());
        $this->assertSame(1, $usuario->bloqueos_temporales_use);

        $durante = $this->intentarLogin($usuario->username_use, 'Secreta123');
        $this->assertGuest();
        $this->assertStringContainsString('Demasiados intentos fallidos', $this->errorDe($durante));

        $this->travel(6)->minutes();

        $this->intentarLogin($usuario->username_use, 'Secreta123')->assertRedirect();
        $this->assertAuthenticatedAs($usuario);

        $usuario->refresh();
        $this->assertSame(0, $usuario->intentos_fallidos_use);
        $this->assertNotNull($usuario->ultimo_login_use);
        $this->assertSame('127.0.0.1', $usuario->ultimo_login_ip_use);
    }

    public function test_tres_bloqueos_temporales_en_24_horas_causan_bloqueo_permanente(): void
    {
        $usuario = $this->crearUsuario('Obrero');

        for ($bloqueo = 1; $bloqueo <= 3; $bloqueo++) {
            for ($i = 0; $i < 3; $i++) {
                $this->intentarLogin($usuario->username_use, 'incorrecta');
            }
            $this->travel(6)->minutes();
        }

        $usuario->refresh();
        $this->assertTrue($usuario->bloqueado_use);
        $this->assertSame(Bloqueos::MOTIVO_TEMPORALES, $usuario->motivo_bloqueo_use);

        $this->travel(2)->days();

        $respuesta = $this->intentarLogin($usuario->username_use, 'Secreta123');
        $this->assertGuest();
        $this->assertStringContainsString('Comunícate con un administrador', $this->errorDe($respuesta));

        $this->assertTrue(BitacoraModelo::where('usuario_id', $usuario->id_use)->where('accion', 'bloqueo_permanente')->where('nivel', 'critico')->exists());
        $this->assertSame(2, BitacoraModelo::where('usuario_id', $usuario->id_use)->where('accion', 'bloqueo_temporal')->count());
    }

    public function test_bloqueos_fuera_de_la_ventana_no_acumulan(): void
    {
        $usuario = $this->crearUsuario('Obrero');

        for ($bloqueo = 1; $bloqueo <= 3; $bloqueo++) {
            for ($i = 0; $i < 3; $i++) {
                $this->intentarLogin($usuario->username_use, 'incorrecta');
            }
            $this->travel(13)->hours();
        }

        $usuario->refresh();
        $this->assertFalse($usuario->bloqueado_use);
    }

    public function test_usuario_inexistente_recibe_el_mismo_mensaje_y_se_limita_por_ip(): void
    {
        $inexistente = 'nadie' . uniqid() . '@prueba.test';
        $existente = $this->crearUsuario('Obrero');

        $this->assertSame(
            $this->errorDe($this->intentarLogin($existente->username_use, 'mala')),
            $this->errorDe($this->intentarLogin($inexistente, 'mala'))
        );

        $this->intentarLogin($inexistente, 'mala');
        $tercera = $this->intentarLogin($inexistente, 'mala');
        $this->assertStringContainsString('Demasiados intentos fallidos', $this->errorDe($tercera));

        $cuarta = $this->intentarLogin($inexistente, 'mala');
        $this->assertStringContainsString('Demasiados intentos fallidos', $this->errorDe($cuarta));

        $fallido = BitacoraModelo::where('accion', 'login_fallido')->where('nombre_usuario', $inexistente)->first();
        $this->assertNotNull($fallido);
        $this->assertNull($fallido->usuario_id);
        $this->assertTrue(BitacoraModelo::where('accion', 'login_bloqueado')->where('nombre_usuario', $inexistente)->exists());

        $this->travel(6)->minutes();
        $this->assertSame(Bloqueos::MENSAJE_GENERICO, $this->errorDe($this->intentarLogin($inexistente, 'mala')));
    }

    public function test_la_bitacora_registra_login_fallido_exitoso_y_logout_sin_contrasena(): void
    {
        $usuario = $this->crearUsuario('Obrero');

        $this->intentarLogin($usuario->username_use, 'ClaveEquivocada99');
        $this->intentarLogin($usuario->username_use, 'Secreta123');
        $this->assertAuthenticatedAs($usuario);
        $this->post('/logout');
        $this->assertGuest();

        $acciones = BitacoraModelo::where('usuario_id', $usuario->id_use)->pluck('accion')->all();
        $this->assertContains('login_fallido', $acciones);
        $this->assertContains('login_exitoso', $acciones);
        $this->assertContains('logout', $acciones);

        $todo = BitacoraModelo::where('usuario_id', $usuario->id_use)->get()->toJson();
        $this->assertStringNotContainsString('ClaveEquivocada99', $todo);
        $this->assertStringNotContainsString('Secreta123', $todo);
    }

    public function test_tres_llaves_maestras_incorrectas_cierran_sesion_y_bloquean(): void
    {
        $admin = $this->crearUsuario('Administrador');
        $this->actingAs($admin);

        $this->post('/admin/configuracion/master-key/verify', ['master_key_use' => 'mala-1'])->assertSessionHasErrors('master_key_use');
        $this->post('/admin/configuracion/master-key/verify', ['master_key_use' => 'mala-2'])->assertSessionHasErrors('master_key_use');
        $this->post('/admin/configuracion/master-key/verify', ['master_key_use' => 'mala-3'])->assertRedirect(route('login'));

        $this->assertGuest();
        $admin->refresh();
        $this->assertTrue($admin->bloqueado_hasta_use->isFuture());
        $this->assertSame(Bloqueos::MOTIVO_LLAVE, $admin->motivo_bloqueo_use);
        $this->assertSame(3, BitacoraModelo::where('usuario_id', $admin->id_use)->where('accion', 'llave_maestra_incorrecta')->count());
    }

    public function test_llave_maestra_correcta_se_registra(): void
    {
        $admin = $this->crearUsuario('Administrador');

        $this->actingAs($admin)
            ->post('/admin/configuracion/master-key/verify', ['master_key_use' => 'Llave12345'])
            ->assertRedirect(route('admin.configuracion.empleados.index'));

        $this->assertTrue(BitacoraModelo::where('usuario_id', $admin->id_use)->where('accion', 'llave_maestra_correcta')->exists());
    }

    public function test_usuario_bloqueado_mientras_navega_es_desconectado(): void
    {
        $usuario = $this->crearUsuario('Obrero');
        $this->actingAs($usuario)->get('/home');

        Bloqueos::aplicarBloqueoPermanente(Auth::user(), Bloqueos::MOTIVO_ACCESOS);

        $this->get('/home')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_no_se_puede_restablecer_la_contrasena_sin_responder_las_preguntas(): void
    {
        $usuario = $this->crearUsuario('Obrero', 'Secreta123', [
            'security_questions_use' => [['question' => '¿Color favorito?', 'answer' => Hash::make('azul')]],
        ]);

        $this->post('/password/recover', ['email' => $usuario->username_use])->assertOk();
        $this->post('/password/recover/reset-password', ['password' => 'Hackeada123', 'password_confirmation' => 'Hackeada123'])
            ->assertRedirect(route('password.recover.email'));

        $this->assertTrue(Hash::check('Secreta123', $usuario->fresh()->getOriginal('password_use')));

        $this->post('/password/recover/verify', ['answers' => ['azul']])->assertOk();
        $this->post('/password/recover/reset-password', ['password' => 'NuevaClave123', 'password_confirmation' => 'NuevaClave123'])
            ->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('NuevaClave123', $usuario->fresh()->getOriginal('password_use')));
        $this->assertTrue(BitacoraModelo::where('usuario_id', $usuario->id_use)->where('accion', 'recuperacion_reseteo_password')->exists());
    }

    public function test_respuestas_de_seguridad_incorrectas_aplican_bloqueo_temporal(): void
    {
        $usuario = $this->crearUsuario('Obrero', 'Secreta123', [
            'security_questions_use' => [['question' => '¿Color favorito?', 'answer' => Hash::make('azul')]],
        ]);

        $this->post('/password/recover', ['email' => $usuario->username_use])->assertOk();
        $this->post('/password/recover/verify', ['answers' => ['rojo']])->assertSessionHasErrors('answers');
        $this->post('/password/recover/verify', ['answers' => ['verde']])->assertSessionHasErrors('answers');
        $this->post('/password/recover/verify', ['answers' => ['negro']])->assertRedirect(route('password.recover.email'));

        $usuario->refresh();
        $this->assertTrue($usuario->bloqueado_hasta_use->isFuture());
        $this->assertSame(Bloqueos::MOTIVO_RECUPERACION, $usuario->motivo_bloqueo_use);
        $this->assertSame(3, BitacoraModelo::where('usuario_id', $usuario->id_use)->where('accion', 'recuperacion_preguntas_incorrectas')->count());
    }

    protected function tearDown(): void
    {
        Auth::forgetGuards();
        parent::tearDown();
    }
}
