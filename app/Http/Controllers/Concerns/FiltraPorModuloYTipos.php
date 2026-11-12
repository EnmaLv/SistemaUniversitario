<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Modulo;
use App\Models\TipoProducto;
use App\Support\Inventario\FiltroTipoProducto;
use Illuminate\Http\Request;

trait FiltraPorModuloYTipos
{
    private const MODULOS_SIN_RESTRICCION = ['administracion'];

    protected function moduloActual(): ?Modulo
    {
        $key = session('modulo_activo');
        if ($key !== null && $key !== '') {
            $modulo = Modulo::where('key_mod', $key)->whereNull('deleted_at')->first();
            if ($modulo) {
                return $modulo;
            }
        }

        foreach (['modulo_actual_id', 'modulo_id', 'mod_id', 'modulo_actual', 'modulo'] as $clave) {
            $valor = session($clave);

            if ($valor instanceof Modulo) {
                return $valor;
            }

            if ($valor !== null && $valor !== '') {
                $modulo = is_numeric($valor)
                    ? Modulo::whereKey((int) $valor)->first()
                    : Modulo::where('key_mod', $valor)->whereNull('deleted_at')->first();

                if ($modulo) {
                    return $modulo;
                }
            }
        }

        $param = request()->route('modulo') ?? request()->input('modulo');
        if ($param !== null && $param !== '') {
            $modulo = is_numeric($param)
                ? Modulo::whereKey((int) $param)->first()
                : Modulo::where('key_mod', $param)->whereNull('deleted_at')->first();

            if ($modulo) {
                return $modulo;
            }
        }

        $routeName = (string) (request()->route()?->getName() ?? '');
        if ($routeName !== '') {
            $partes = explode('.', $routeName);
            if (($partes[0] ?? null) === 'admin' && !empty($partes[1])) {
                $modulo = Modulo::where('key_mod', $partes[1])->whereNull('deleted_at')->first();
                if ($modulo) {
                    return $modulo;
                }
            }
        }

        return null;
    }

    protected function tiposDelModulo(): ?array
    {
        $modulo = $this->moduloActual();
        if ($modulo === null) {
            return null;
        }

        if (in_array($modulo->key_mod, self::MODULOS_SIN_RESTRICCION, true)) {
            return null;
        }

        $tipos = TipoProducto::idsParaModulo((int) $modulo->id_mod);

        return $tipos === [] ? null : $tipos;
    }

    protected function resolverTipoProductoRequest(Request $request): ?int
    {
        $permitidos   = $this->tiposDelModulo();
        $tipoProducto = FiltroTipoProducto::resolver($request);

        if ($permitidos !== null && !in_array($tipoProducto, $permitidos, true)) {
            $tipoProducto = $permitidos[0] ?? null;
        }

        return $tipoProducto;
    }

    protected function opcionesTipoProducto()
    {
        $permitidos    = $this->tiposDelModulo();
        $tiposProducto = FiltroTipoProducto::opciones();

        if ($permitidos !== null) {
            $tiposProducto = $tiposProducto
                ->filter(fn ($t) => in_array((int) $t->id_tpr, $permitidos, true))
                ->values();
        }

        return $tiposProducto;
    }
}