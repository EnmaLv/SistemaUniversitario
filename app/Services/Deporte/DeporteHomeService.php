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
            ->with(['persona', 'producto', 'detalles.producto'])
            ->where('estado_prd', PrestamoDeportivo::ESTADO_ACTIVO)
            ->orderBy('fecha_devolucion_esperada_prd')
            ->get();

        $prestamosVencidos = PrestamoDeportivo::query()
            ->with(['persona', 'producto', 'detalles.producto'])
            ->where('estado_prd', PrestamoDeportivo::ESTADO_VENCIDO)
            ->orderBy('fecha_devolucion_esperada_prd')
            ->get();

        $sanciones = SancionDeportiva::query()
            ->with(['persona', 'prestamo.producto', 'prestamo.detalles.producto'])
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
            ->selectRaw('SUM(cantidad_convertida_total_int) AS cantidad')
            ->pluck('cantidad', 'pdt_id');

        $prestados = DB::table('detalle_prestamo_deportivo as detalle')
            ->join('prestamo_deportivo as prestamo', 'prestamo.id_prd', '=', 'detalle.prd_id')
            ->whereNull('detalle.deleted_at')
            ->whereNull('prestamo.deleted_at')
            ->whereIn('prestamo.estado_prd', [PrestamoDeportivo::ESTADO_ACTIVO, PrestamoDeportivo::ESTADO_VENCIDO])
            ->when($sedeId !== null, fn ($q) => $q->where('prestamo.sed_id', $sedeId))
            ->groupBy('detalle.pdt_id')
            ->select('detalle.pdt_id')
            ->selectRaw('SUM(detalle.cantidad_dpd) AS cantidad')
            ->pluck('cantidad', 'pdt_id');
        $oficina = mb_strtolower(TipoProducto::MATERIAL_OFICINA);

        return TipoProducto::productosDeTipos(array_values(PrestamoDeportivo::TIPOS_PRODUCTO))
            ->where('producto.status_id', 1)
            ->orderBy('producto.nombre_pdt')
            ->get(['producto.id_pdt', 'producto.nombre_pdt', 'producto.stock_minimo_pdt', 'tipo_producto.nombre_tpr'])
            ->map(function ($producto) use ($existencias, $prestados, $oficina) {
                $producto->oficina = mb_strtolower((string) $producto->nombre_tpr) === $oficina;
                $producto->cantidad = (float) ($existencias[$producto->id_pdt] ?? 0);
                $producto->prestados = (float) ($prestados[$producto->id_pdt] ?? 0);
                $producto->minimo = (float) $producto->stock_minimo_pdt;
                $producto->alerta = $producto->cantidad <= $producto->minimo;

                return $producto;
            })
            ->sortByDesc('alerta')
            ->values();
    }
}
