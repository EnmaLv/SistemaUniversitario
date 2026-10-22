<?php

namespace App\Services\Deporte;

use App\Models\Deporte\PrestamoDeportivo;
use App\Models\Deporte\SancionDeportiva;
use App\Models\TipoProducto;
use App\Support\Deportes\ServicioEventos;
use App\Support\Sedes\SedeActiva;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DeporteHomeService
{
    public function __construct(private PrestamoService $prestamos)
    {
    }

    public function datos(): array
    {
        $this->prestamos->marcarVencidos();

        $hoy = Carbon::today();
        $sedeId = SedeActiva::id();

        $prestamosActivos = PrestamoDeportivo::query()
            ->with(['persona', 'producto'])
            ->where('estado_prd', PrestamoDeportivo::ESTADO_ACTIVO)
            ->orderBy('fecha_devolucion_esperada_prd')
            ->get();

        $prestamosVencidos = PrestamoDeportivo::query()
            ->with(['persona', 'producto'])
            ->where('estado_prd', PrestamoDeportivo::ESTADO_VENCIDO)
            ->orderBy('fecha_devolucion_esperada_prd')
            ->get();

        $sanciones = SancionDeportiva::query()
            ->with(['persona', 'prestamo.producto'])
            ->activas()
            ->orderByDesc('fecha_sancion_snd')
            ->get();

        return [
            'hoy'               => ServicioEventos::listar($hoy, $hoy, null, false),
            'proximos'          => ServicioEventos::listar($hoy->copy()->addDay(), $hoy->copy()->addDays(7), null, false)->take(8)->values(),
            'prestamosActivos'  => $prestamosActivos,
            'prestamosVencidos' => $prestamosVencidos,
            'sanciones'         => $sanciones,
            'implementos'       => $this->implementos($sedeId),
            'sedeNombre'        => $sedeId ? DB::table('sede')->where('id_sed', $sedeId)->value('nombre_sed') : null,
        ];
    }

    private function implementos(?int $sedeId): Collection
    {
        $existencias = DB::table('inventario_total')
            ->when($sedeId !== null, fn ($q) => $q->where('sed_id', $sedeId))
            ->groupBy('pdt_id')
            ->select('pdt_id')
            ->selectRaw('SUM(cantidad_total_int) AS cantidad')
            ->pluck('cantidad', 'pdt_id');

        $prestados = PrestamoDeportivo::query()
            ->whereIn('estado_prd', [PrestamoDeportivo::ESTADO_ACTIVO, PrestamoDeportivo::ESTADO_VENCIDO])
            ->groupBy('pdt_id')
            ->select('pdt_id')
            ->selectRaw('SUM(cantidad_prd) AS cantidad')
            ->pluck('cantidad', 'pdt_id');

        return TipoProducto::productosDeTipos([TipoProducto::IMPLEMENTO_DEPORTIVO])
            ->where('producto.status_id', 1)
            ->orderBy('producto.nombre_pdt')
            ->get(['producto.id_pdt', 'producto.nombre_pdt', 'producto.stock_minimo_pdt'])
            ->map(function ($producto) use ($existencias, $prestados) {
                $producto->cantidad = (float) ($existencias[$producto->id_pdt] ?? 0);
                $producto->prestados = (int) ($prestados[$producto->id_pdt] ?? 0);
                $producto->minimo = (float) $producto->stock_minimo_pdt;
                $producto->alerta = $producto->cantidad <= $producto->minimo;

                return $producto;
            })
            ->sortByDesc('alerta')
            ->values();
    }
}
