<?php

namespace App\Support\Inventario;

use Illuminate\Support\Facades\DB;

class PrecioVigente
{
    public static function de(int $productoId): array
    {
        $precio = DB::table('precio_producto')
            ->where('pdt_id', $productoId)
            ->whereNull('deleted_at')
            ->orderByDesc('id_ppr')
            ->first(['id_ppr', 'costo_usd_ppr', 'precio_usd_ppr', 'margen_ppr']);

        $usd = null;

        if ($precio) {
            $costo = (float) ($precio->costo_usd_ppr ?? 0);
            $usd = $costo > 0 ? $costo : ((float) ($precio->precio_usd_ppr ?? 0) ?: null);
        }

        $tasa = static::tasa();
        $bs = $usd !== null && $tasa !== null ? round($usd * $tasa['valor'], 2) : null;

        return [
            'registrado' => $precio !== null,
            'usd'        => $usd === null ? null : round($usd, 2),
            'bs'         => $bs,
            'tasa'       => $tasa['valor'] ?? null,
            'fecha_tasa' => $tasa['fecha'] ?? null,
        ];
    }

    public static function tasa(): ?array
    {
        $tasa = DB::table('tasa_cambio')
            ->whereNull('deleted_at')
            ->where('promedio_tca', '>', 0)
            ->orderByDesc('fecha_vigencia_tca')
            ->orderByDesc('id_tca')
            ->first(['promedio_tca', 'fecha_vigencia_tca']);

        if (! $tasa) {
            return null;
        }

        return [
            'valor' => (float) $tasa->promedio_tca,
            'fecha' => $tasa->fecha_vigencia_tca,
        ];
    }
}
