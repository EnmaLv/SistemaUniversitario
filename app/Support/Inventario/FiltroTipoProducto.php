<?php

namespace App\Support\Inventario;

use App\AdminLTE\Filters\ModuleFilter;
use App\Models\TipoProducto;
use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FiltroTipoProducto
{
    public const PARAMETRO = 'tipo';

    public const TODOS = 'todos';

    public static function resolver(Request $request): ?int
    {
        if ($request->has(self::PARAMETRO)) {
            return self::normalizar($request->input(self::PARAMETRO));
        }

        return self::predeterminado($request->user());
    }

    public static function opciones(): Collection
    {
        return TipoProducto::query()
            ->where('status_id', TipoProducto::STATUS_ACTIVO)
            ->orderBy('id_tpr')
            ->get(['id_tpr', 'nombre_tpr']);
    }

    public static function valorUrl(?int $tipo): string
    {
        return $tipo === null ? self::TODOS : (string) $tipo;
    }

    public static function nombre(?int $tipo): string
    {
        if ($tipo === null) {
            return 'Todos';
        }

        return (string) (TipoProducto::query()->whereKey($tipo)->value('nombre_tpr') ?? 'Todos');
    }

    public static function predeterminado($usuario): ?int
    {
        if (! $usuario instanceof Usuario || $usuario->esSuperAdmin()) {
            return null;
        }

        $modulos = self::modulosPermitidos($usuario);

        if ($modulos === []) {
            return null;
        }

        $asociados = DB::table('tipo_producto')
            ->join('modulo', 'modulo.id_mod', '=', 'tipo_producto.mod_id')
            ->whereIn('modulo.key_mod', $modulos)
            ->where('tipo_producto.status_id', TipoProducto::STATUS_ACTIVO)
            ->whereNull('tipo_producto.deleted_at')
            ->get(['modulo.key_mod', 'tipo_producto.id_tpr']);

        if ($asociados->pluck('key_mod')->unique()->count() !== 1 || $asociados->count() !== 1) {
            return null;
        }

        return (int) $asociados->first()->id_tpr;
    }

    public static function aplicar($consulta, ?int $tipo, string $columnaProducto)
    {
        if ($tipo === null) {
            return $consulta;
        }

        return $consulta->whereIn($columnaProducto, self::productosDelTipo($tipo));
    }

    public static function aplicarPorLote($consulta, ?int $tipo, string $columnaLote)
    {
        if ($tipo === null) {
            return $consulta;
        }

        return $consulta->whereIn($columnaLote, function ($sub) use ($tipo) {
            $sub->select('lote.id_lot')
                ->from('lote')
                ->whereIn('lote.pdt_id', self::productosDelTipo($tipo));
        });
    }

    private static function productosDelTipo(int $tipo): Closure
    {
        return function ($sub) use ($tipo) {
            $sub->select('producto.id_pdt')
                ->from('producto')
                ->join('categoria_producto', 'categoria_producto.id_cap', '=', 'producto.cap_id')
                ->where('categoria_producto.tpr_id', $tipo);
        };
    }

    private static function normalizar($valor): ?int
    {
        if (! is_scalar($valor) || ! ctype_digit((string) $valor)) {
            return null;
        }

        $id = (int) $valor;

        return self::opciones()->contains('id_tpr', $id) ? $id : null;
    }

    private static function modulosPermitidos(Usuario $usuario): array
    {
        if (! app()->bound('session')) {
            return [];
        }

        app(ModuleFilter::class)->asegurarSesion($usuario->id_use);

        return array_values((array) session('modulos_permitidos', []));
    }
}
