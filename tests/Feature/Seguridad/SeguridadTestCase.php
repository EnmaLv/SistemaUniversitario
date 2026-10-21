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
        \App\Support\Sedes\SedeActiva::olvidar();

        $this->iniciarTransaccion();
    }

    protected static ?string $errorPreparacion = null;

    protected static function prepararBaseDatos(): void
    {
        if (static::$basePreparada) {
            return;
        }

        if (static::$errorPreparacion !== null) {
            throw new \RuntimeException('La base de pruebas no se pudo preparar: ' . static::$errorPreparacion);
        }

        try {
            static::construirBaseDatos();
        } catch (\Throwable $e) {
            static::$errorPreparacion = $e->getMessage();
            throw $e;
        }
    }

    protected static function construirBaseDatos(): void
    {
        $huella = static::huellaMigraciones();
        $archivoHuella = storage_path('framework/testing/esquema_' . DB::connection()->getDatabaseName() . '.hash');

        if (! is_file($archivoHuella) || trim((string) file_get_contents($archivoHuella)) !== $huella) {
            Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true]);
            file_put_contents($archivoHuella, $huella);
        } else {
            Artisan::call('migrate', ['--force' => true]);
        }

        if (! DB::table('perfil')->exists()) {
            DB::table('perfil')->insert([
                ['nombre_perfil' => 'Usuario', 'status_id' => 1],
                ['nombre_perfil' => 'Estudiante', 'status_id' => 1],
            ]);
        }

        foreach (['Sede de pruebas', 'Sede B de pruebas'] as $nombreSede) {
            if (! DB::table('sede')->where('nombre_sed', $nombreSede)->exists()) {
                DB::table('sede')->insert([
                    'nombre_sed'    => $nombreSede,
                    'direccion_sed' => 'Dirección de pruebas',
                    'telefono_sed'  => '0424-0000000',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (! DB::table('modulos')->exists()) {
            (new ModulosSeeder())->run();
        }

        (new PermisosSeeder())->run();

        static::$basePreparada = true;
    }

    protected static function huellaMigraciones(): string
    {
        $archivos = collect(array_merge(
            \Illuminate\Support\Facades\File::allFiles(database_path('migrations')),
            \Illuminate\Support\Facades\File::allFiles(database_path('seeders'))
        ))
            ->filter(fn ($archivo) => $archivo->getExtension() === 'php')
            ->sortBy(fn ($archivo) => $archivo->getRelativePathname())
            ->map(fn ($archivo) => $archivo->getRelativePathname() . ':' . md5_file($archivo->getPathname()))
            ->implode('|');

        return md5($archivos);
    }

    protected function sedeA(): int
    {
        return (int) DB::table('sede')->where('nombre_sed', 'Sede de pruebas')->value('id_sed');
    }

    protected function sedeB(): int
    {
        return (int) DB::table('sede')->where('nombre_sed', 'Sede B de pruebas')->value('id_sed');
    }

    protected function crearUsuario(array|string $roles = [], string $password = 'Secreta123', array $extra = [], ?int $sedeId = null): Usuario
    {
        $n = ++static::$consecutivo . '_' . bin2hex(random_bytes(3));

        $persona = Persona::create([
            'nombres_per'   => 'Prueba',
            'apellidos_per' => 'Seguridad',
            'cedula_per'   => (string) random_int(10000000, 99999999),
            'telefono_per' => '0424-0000000',
            'genero_per'   => 'Otro',
            'correo_per'    => "persona{$n}@prueba.test",
            'id_perfil'        => DB::table('perfil')->value('id_perfil'),
            'sed_id'          => $sedeId ?? $this->sedeA(),
        ]);

        $usuario = new Usuario([
            'per_id' => $persona->id_per,
            'id_perfil'  => DB::table('perfil')->value('id_perfil'),
            'username'   => "usuario{$n}@prueba.test",
        ]);
        $usuario->password = $password;
        $usuario->master_key = 'Llave12345';
        $usuario->forceFill($extra)->save();

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
