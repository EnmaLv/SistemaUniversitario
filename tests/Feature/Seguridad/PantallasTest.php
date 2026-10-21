<?php

namespace Tests\Feature\Seguridad;

use App\Models\Usuario;
use Illuminate\Support\Facades\Route;
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

    private function recorrer(string $username): array
    {
        config(['seguridad.max_accesos_denegados' => 100000]);

        $usuario = Usuario::where('username', $username)->firstOrFail();
        $usuario->forceFill(['debe_cambiar_password' => false])->saveQuietly();

        $this->actingAs($usuario)->withSession(['master_key_validated' => true, 'ultima_actividad' => time()]);

        $fallos = [];

        foreach ($this->rutasGet() as $uri) {
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

    public function test_las_pantallas_de_la_psicologa_no_fallan(): void
    {
        $this->assertSame([], $this->recorrer('psicologa.laura@example.com'));
    }

    public function test_las_pantallas_del_paciente_no_fallan(): void
    {
        $this->assertSame([], $this->recorrer('america@example.com'));
    }
}
