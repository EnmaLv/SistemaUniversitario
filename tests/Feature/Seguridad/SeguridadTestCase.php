<?php

namespace Tests\Feature\Seguridad;

use App\Models\Persona;
use App\Models\Rol;
use App\Models\Usuario;
use App\Support\Permisos\MapaRutas;
use Database\Seeders\ModulosSeeder;
use Database\Seeders\PermisosSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

abstract class SeguridadTestCase extends TestCase
{
    use DatabaseTransactions {
        beginDatabaseTransaction as protected iniciarTransaccion;
    }

    protected static bool $basePreparada = false;

    protected static int $consecutivo = 0;

    public function beginDatabaseTransaction()
    {
        static::prepararBaseDatos();
        Cache::flush();
        MapaRutas::limpiar();

        $this->iniciarTransaccion();
    }

    protected static function prepararBaseDatos(): void
    {
        if (static::$basePreparada) {
            return;
        }

        Artisan::call('migrate', ['--force' => true]);

        if (! DB::table('perfil')->exists()) {
            DB::table('perfil')->insert([
                ['nombre_perfil' => 'Usuario', 'estado' => 1],
                ['nombre_perfil' => 'Estudiante', 'estado' => 1],
            ]);
        }

        if (! DB::table('sede')->exists()) {
            DB::table('sede')->insert([
                'nombre'     => 'Sede de pruebas',
                'direccion'  => 'Dirección de pruebas',
                'telefono'   => '0424-0000000',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (! DB::table('modulos')->exists()) {
            (new ModulosSeeder())->run();
        }

        if (! DB::table('permiso')->exists() || ! DB::table('vista')->where('key', 'seguridad_bloqueos')->exists()) {
            (new PermisosSeeder())->run();
        }

        static::$basePreparada = true;
    }

    protected function crearUsuario(array|string $roles = [], string $password = 'Secreta123', array $extra = []): Usuario
    {
        $n = ++static::$consecutivo . '_' . bin2hex(random_bytes(3));

        $persona = Persona::create([
            'nombre_persona'   => 'Prueba',
            'apellido_persona' => 'Seguridad',
            'cedula_persona'   => (string) random_int(10000000, 99999999),
            'telefono_persona' => '0424-0000000',
            'genero_persona'   => 'Otro',
            'email_persona'    => "persona{$n}@prueba.test",
            'id_perfil'        => DB::table('perfil')->value('id_perfil'),
            'id_sede'          => DB::table('sede')->value('id'),
        ]);

        $usuario = Usuario::create(array_merge([
            'id_persona' => $persona->id_persona,
            'id_perfil'  => DB::table('perfil')->value('id_perfil'),
            'username'   => "usuario{$n}@prueba.test",
            'password'   => $password,
            'master_key' => 'Llave12345',
        ], $extra));

        $ids = Rol::whereIn('nombre', (array) $roles)->pluck('id_rol')->all();
        if ($ids) {
            $usuario->roles()->sync($ids);
        }

        return $usuario->fresh();
    }

    protected function limpiarLimitadores(string $username, string $ip = '127.0.0.1'): void
    {
        RateLimiter::clear('login:' . sha1(mb_strtolower($username) . '|' . $ip));
    }
}
