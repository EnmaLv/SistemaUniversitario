<?php

namespace Tests\Feature\Seguridad;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Reflector;
use Illuminate\Support\Str;

class PantallasTest extends SeguridadTestCase
{
    private const PREFIJOS_EXCLUIDOS = ['api/', 'sanctum/', 'livewire', '_ignition', 'storage/', 'broadcasting/', 'up'];

    private const PATRONES_EXCLUIDOS = ['pdf', 'excel', 'word', 'export', 'download', 'descargar', 'zip', 'csv', 'imprimir', 'print', 'logout', 'reporte'];

    private function rutasGet(): array
    {
        $rutas = [];

        foreach (Route::getRoutes() as $ruta) {
            $uri = $ruta->uri();

            if (! in_array('GET', $ruta->methods(), true) || preg_match('/\{[^}?]+\}/', $uri)) {
                continue;
            }

            if (Str::startsWith($uri, self::PREFIJOS_EXCLUIDOS) || Str::contains(Str::lower($uri . ' ' . $ruta->getName()), self::PATRONES_EXCLUIDOS)) {
                continue;
            }

            $rutas[] = '/' . ltrim(preg_replace('/\{[^}]+\?\}/', '', $uri), '/');
        }

        return array_values(array_unique($rutas));
    }

    private function rutasConParametro(): array
    {
        $rutas = [];

        foreach (Route::getRoutes() as $ruta) {
            $uri = $ruta->uri();
            $parametros = $ruta->parameterNames();

            if (! in_array('GET', $ruta->methods(), true) || count($parametros) !== 1 || preg_match('/\{[^}]+\?\}/', $uri)) {
                continue;
            }

            if (Str::startsWith($uri, self::PREFIJOS_EXCLUIDOS) || Str::contains(Str::lower($uri . ' ' . $ruta->getName()), [...self::PATRONES_EXCLUIDOS, 'media/', 'password/reset', 'notificaciones', 'enviar-correo', 'documento', 'archivos/ver'])) {
                continue;
            }

            $valor = $this->valorParametro($ruta);

            if ($valor !== null) {
                $rutas[] = '/' . ltrim(str_replace('{' . $parametros[0] . '}', (string) $valor, $uri), '/');
            }
        }

        return array_values(array_unique($rutas));
    }

    private function valorParametro($ruta): int|string|null
    {
        foreach ($ruta->signatureParameters() as $parametro) {
            $clase = Reflector::getParameterClassName($parametro);

            if ($clase && is_subclass_of($clase, Model::class)) {
                $modelo = new $clase;

                return $clase::query()->withoutGlobalScopes()->orderBy($modelo->getKeyName())->value($modelo->getRouteKeyName());
            }
        }

        return 1;
    }

    private function recorrer(string $username, bool $conParametros = false): array
    {
        config(['seguridad.max_accesos_denegados' => 100000]);

        $usuario = Usuario::where('username_use', $username)->firstOrFail();
        $usuario->forceFill(['debe_cambiar_password_use' => false])->saveQuietly();

        $this->actingAs($usuario)->withSession(['master_key_validated' => true, 'ultima_actividad' => time()]);

        $fallos = [];
        $redirector = app('redirect');

        foreach ($conParametros ? $this->rutasConParametro() : $this->rutasGet() as $uri) {
            app()->instance('redirect', $redirector);
            $respuesta = $this->get($uri);

            if ($respuesta->getStatusCode() >= 500) {
                $mensaje = $respuesta->exception ? Str::limit($respuesta->exception->getMessage(), 220) : '';
                $fallos[] = "{$uri} → {$respuesta->getStatusCode()} {$mensaje}";
            }
        }

        return $fallos;
    }

    public function test_las_pantallas_del_superadmin_no_fallan(): void
    {
        $this->assertSame([], $this->recorrer('admin@example.com'));
    }

    public function test_las_pantallas_con_parametro_del_superadmin_no_fallan(): void
    {
        $this->assertSame([], $this->recorrer('admin@example.com', true));
    }

    public function test_las_pantallas_de_la_psicologa_no_fallan(): void
    {
        $this->assertSame([], $this->recorrer('psicologa.laura@example.com'));
    }

    public function test_las_pantallas_del_paciente_no_fallan(): void
    {
        $this->assertSame([], $this->recorrer('america@example.com'));
    }
}
