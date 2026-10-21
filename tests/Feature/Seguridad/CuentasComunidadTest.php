<?php

namespace Tests\Feature\Seguridad;

use App\Livewire\Archivos;
use App\Models\Archivo;
use App\Models\Bitacora as BitacoraModelo;
use App\Models\Persona;
use App\Models\Usuario;
use App\Support\Cuentas\Cedula;
use App\Support\Cuentas\CuentasComunidad;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

class CuentasComunidadTest extends SeguridadTestCase
{
    private function crearPersona(?string $cedula = null): Persona
    {
        return Persona::create([
            'nombres_per'   => 'ESTUDIANTE',
            'apellidos_per' => 'PRUEBA',
            'cedula_per'   => $cedula ?? (string) random_int(10000000, 29999999),
            'telefono_per' => '04140000000',
            'genero_per'   => 'FEMENINO',
            'correo_per'    => 'estudiante' . random_int(1, 999999) . '@prueba.test',
            'id_perfil'        => DB::table('perfil')->value('id_perfil'),
            'sed_id'          => DB::table('sede')->value('id_sed'),
        ]);
    }

    private function slugsDe(Usuario $usuario): array
    {
        return $usuario->roles()->pluck('slug_rol')->sort()->values()->all();
    }

    public function test_normaliza_la_cedula_como_el_login(): void
    {
        $this->assertSame('12345678', Cedula::normalizar('V-12.345.678'));
        $this->assertSame('8765432', Cedula::normalizar(' e 8 765 432 '));
        $this->assertSame('30111222', Cedula::normalizar('30111222'));
        $this->assertNull(Cedula::normalizar(''));
        $this->assertNull(Cedula::normalizar('12A45678'));
        $this->assertNull(Cedula::normalizar('12345'));
    }

    public function test_registrar_una_persona_crea_su_cuenta_con_la_cedula(): void
    {
        $persona = $this->crearPersona('27111333');

        $usuario = CuentasComunidad::asegurarCuenta($persona);

        $this->assertNotNull($usuario);
        $this->assertSame('27111333', $usuario->username_use);
        $this->assertTrue(Hash::check('27111333', $usuario->getOriginal('password_use')));
        $this->assertTrue($usuario->debe_cambiar_password_use);
        $this->assertSame(['becario', 'paciente'], $this->slugsDe($usuario));
        $this->assertTrue(BitacoraModelo::where('accion', 'cuentas_comunidad_generadas')->exists());
    }

    public function test_si_la_persona_ya_tiene_usuario_solo_agrega_los_roles(): void
    {
        $usuario = $this->crearUsuario('Obrero');
        $antes = Usuario::count();

        CuentasComunidad::asegurarCuenta($usuario->persona);
        CuentasComunidad::asegurarCuenta($usuario->persona);

        $this->assertSame($antes, Usuario::count());
        $this->assertSame(['becario', 'obrero', 'paciente'], $this->slugsDe($usuario->fresh()));
        $this->assertFalse((bool) $usuario->fresh()->debe_cambiar_password_use);
    }

    public function test_el_login_acepta_cedula_o_correo(): void
    {
        $persona = $this->crearPersona('26444555');
        CuentasComunidad::asegurarCuenta($persona);

        $this->post('/login', ['email' => 'V-26.444.555', 'password' => '26444555']);
        $this->assertAuthenticated();
        $this->post('/logout');

        $existente = $this->crearUsuario('Obrero');
        $this->post('/login', ['email' => strtoupper($existente->username_use), 'password' => 'Secreta123']);
        $this->assertAuthenticatedAs($existente->fresh());
    }

    public function test_el_bloqueo_cuenta_los_intentos_con_cedula_y_con_correo(): void
    {
        $usuario = $this->crearUsuario('Obrero');
        $cedula = $usuario->persona->cedula_per;

        $this->post('/login', ['email' => $cedula, 'password' => 'incorrecta']);
        $this->post('/login', ['email' => $usuario->username_use, 'password' => 'incorrecta']);
        $this->post('/login', ['email' => 'V-' . $cedula, 'password' => 'incorrecta']);

        $this->assertNotNull($usuario->fresh()->bloqueado_hasta_use);

        $this->post('/login', ['email' => $cedula, 'password' => 'Secreta123']);
        $this->assertGuest();
    }

    public function test_el_cambio_de_contrasena_es_obligatorio_en_el_primer_inicio(): void
    {
        $persona = $this->crearPersona('25666777');
        $usuario = CuentasComunidad::asegurarCuenta($persona);

        $this->actingAs($usuario);

        $this->get('/home')->assertRedirect(route('password.cambiar'));
        $this->getJson('/home')->assertStatus(428);
        $this->get(route('password.cambiar'))->assertOk()->assertSee('Cambiar contraseña');

        $this->post(route('password.cambiar.guardar'), [
            'password_actual'       => '25666777',
            'password'              => 'V-25.666.777',
            'password_confirmation' => 'V-25.666.777',
        ])->assertSessionHasErrors('password');

        $this->post(route('password.cambiar.guardar'), [
            'password_actual'       => '25666777',
            'password'              => 'corta',
            'password_confirmation' => 'corta',
        ])->assertSessionHasErrors('password');

        $this->post(route('password.cambiar.guardar'), [
            'password_actual'       => '25666777',
            'password'              => 'NuevaClave2026',
            'password_confirmation' => 'NuevaClave2026',
        ])->assertRedirect();

        $usuario->refresh();
        $this->assertFalse($usuario->debe_cambiar_password_use);
        $this->assertNotNull($usuario->password_cambiado_en_use);
        $this->assertTrue(Hash::check('NuevaClave2026', $usuario->getOriginal('password_use')));
        $this->assertTrue(BitacoraModelo::where('accion', 'password_cambiado')->where('registro_id', $usuario->id_use)->exists());

        $this->get('/home')->assertDontSee('Debes cambiar tu contraseña');
        $this->assertNotSame(route('password.cambiar'), $this->get('/home')->headers->get('Location'));
    }

    public function test_la_importacion_crea_personas_y_cuentas_y_reporta_errores(): void
    {
        Storage::fake('local');
        $existente = $this->crearPersona('24888999');

        $csv = implode("\n", [
            'Cedula,Nombre,Apellido,Correo',
            'V-23.111.001,Ana,Pérez,ana@prueba.test',
            '23111002,Luis,Gómez,',
            ',Sin,Cedula,',
            'ABC,Mal,Formada,',
            '23111001,Ana,Repetida,',
            '24888999,Estudiante,Actualizada,nuevo@prueba.test',
            '23111003,,SinNombre,',
        ]);

        Livewire::actingAs($this->crearUsuario('Administrador'))
            ->test(Archivos::class)
            ->set('archivo', UploadedFile::fake()->createWithContent('personas.csv', $csv))
            ->call('save')
            ->assertHasNoErrors();

        $archivo = Archivo::latest('id')->first();
        $resumen = $archivo->resumen;

        $this->assertSame('Procesado', $archivo->estado);
        $this->assertSame(7, $resumen['total_filas']);
        $this->assertSame(2, $resumen['personas_creadas']);
        $this->assertSame(1, $resumen['personas_actualizadas']);
        $this->assertSame(4, $resumen['filas_con_error']);
        $this->assertSame(3, $resumen['cuentas']['usuarios_creados']);
        $this->assertSame(
            ['Cédula vacía', 'Cédula mal formada', 'Cédula duplicada en el archivo (ya aparece en la fila 2)', 'Nombre o apellido vacío'],
            array_column($resumen['errores'], 'motivo')
        );

        $this->assertTrue(Usuario::where('username_use', '23111001')->where('debe_cambiar_password_use', true)->exists());
        $this->assertSame('ACTUALIZADA', $existente->fresh()->apellidos_per);
        $this->assertSame('04140000000', $existente->fresh()->telefono_per);

        $evento = BitacoraModelo::where('accion', 'importacion_personas')->latest('id')->first();
        $this->assertNotNull($evento);
        $this->assertStringNotContainsString('23111001', json_encode($evento->datos_modificados) . $evento->observacion);
    }

    public function test_el_comando_crea_las_cuentas_faltantes(): void
    {
        $persona = $this->crearPersona('22333444');

        $this->artisan('usuarios:generar-faltantes')->assertSuccessful();

        $usuario = Usuario::where('per_id', $persona->id_per)->first();
        $this->assertNotNull($usuario);
        $this->assertSame('22333444', $usuario->username_use);
    }
}
