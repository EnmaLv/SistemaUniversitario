<?php

namespace App\Services\Salud;

use App\Exceptions\Salud\StockInsuficienteException;
use App\Models\InventarioSedeLote;
use App\Models\Lote;
use App\Models\salud\Consulta;
use App\Models\salud\DetalleRecetasMedica;
use App\Models\salud\Dispensacion;
use App\Models\salud\RecetasMedica;
use App\Support\Inventario\ServicioInventario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Paso 3: dispensación de medicamentos con descuento FIFO de inventario.
 */
class DispensacionService
{
    public const SEDE_POR_DEFECTO = 1;

    public function __construct(
        private readonly ConversorDosis $conversor,
        private readonly ServicioInventario $inventario,
    ) {}

    public static function sedeDelUsuario($usuario): int
    {
        return (int) (\App\Support\Sedes\SedeActiva::idParaCrear() ?? self::SEDE_POR_DEFECTO);
    }

    /**
     * Indica si el checkbox "dispensar" del ítem viene marcado.
     */
    public static function marcadoParaDispensar(array $item): bool
    {
        return filter_var($item['dispensar'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Lotes vigentes con stock en la sede, ordenados FIFO por vencimiento.
     */
    public function lotesDisponiblesQuery(int $productoId, int $sedeId): Builder
    {
        return Lote::where('pdt_id', $productoId)
            ->where(function ($q) {
                $q->whereNull('fecha_vencimiento_lot')
                    ->orWhere('fecha_vencimiento_lot', '>=', now()->toDateString());
            })
            ->whereHas('inventarioSedeLotes', function ($q) use ($sedeId) {
                $q->where('sed_id', $sedeId)->where('cantidad_convertida_isl', '>', 0);
            })
            ->orderByRaw('fecha_vencimiento_lot IS NULL ASC')
            ->orderBy('fecha_vencimiento_lot', 'asc')
            ->orderBy('id_lot', 'asc');
    }

    /**
     * Lotes FIFO por cada detalle con cantidad pendiente.
     *
     * @return array<int, \Illuminate\Database\Eloquent\Collection>
     */
    public function lotesPorDetalle(Consulta $consulta, int $sedeId): array
    {
        $lotesPorDetalle = [];

        foreach ($consulta->receta->detalles as $detalle) {
            if ($detalle->cantidad_pendiente <= 0) {
                continue;
            }

            $lotesPorDetalle[$detalle->id_drm] = $this->lotesDisponiblesQuery($detalle->pdt_id, $sedeId)
                ->with(['inventarioSedeLotes' => fn($q) => $q->where('sed_id', $sedeId)])
                ->get();
        }

        return $lotesPorDetalle;
    }

    /**
     * Unidades de inventario que equivalen a una unidad de dosis del detalle (null si no hay conversión).
     *
     * @return array<int, float|null>
     */
    public function factoresPorDetalle(Consulta $consulta): array
    {
        return $consulta->receta->detalles
            ->mapWithKeys(fn ($detalle) => [$detalle->id_drm => $this->conversor->factorDetalle($detalle)])
            ->all();
    }

    public function cantidadInventario(DetalleRecetasMedica $detalle, float $cantidadDosis, $cantidadIndicada = null): ?float
    {
        $convertida = $this->conversor->aInventario($detalle, $cantidadDosis);

        if ($convertida !== null && $convertida > 0) {
            return $convertida;
        }

        $indicada = is_numeric($cantidadIndicada) ? round((float) $cantidadIndicada, 2) : 0.0;

        return $indicada > 0 ? $indicada : null;
    }

    /**
     * Registra las entregas, descuenta inventario y actualiza el estado de la receta.
     *
     * @param array $items Ítems validados, indexados por ID de detalle
     * @return array{creados:int, estado:int}
     *
     * @throws StockInsuficienteException
     */
    public function dispensar(Consulta $consulta, array $items, int $sedeId, int $usuarioId): array
    {
        $receta  = $consulta->receta;
        $creados = 0;

        DB::transaction(function () use ($consulta, $receta, $items, $sedeId, $usuarioId, &$creados) {
            $detalles = DetalleRecetasMedica::with('producto.unidad')
                ->where('rme_id', $receta->id_rme)
                ->lockForUpdate()
                ->get();

            foreach ($detalles as $detalle) {
                $item          = $items[$detalle->id_drm] ?? [];
                $cantidadInput = (float) ($item['cantidad'] ?? 0);

                if (!self::marcadoParaDispensar($item) || $cantidadInput <= 0) {
                    continue;
                }

                $cantidad = min($cantidadInput, $detalle->cantidad_pendiente);

                if ($cantidad <= 0) {
                    continue;
                }

                $cantidadInventario = $this->cantidadInventario($detalle, $cantidad, $item['cantidad_inventario'] ?? null);

                if ($cantidadInventario === null) {
                    throw StockInsuficienteException::sinCantidadInventario($detalle->producto->nombre_pdt ?? 'producto');
                }

                $this->dispensarDetalle(
                    $consulta,
                    $receta,
                    $detalle,
                    $cantidad,
                    $cantidadInventario,
                    $item['observaciones'] ?? null,
                    $sedeId,
                    $usuarioId
                );

                $creados++;
            }
        });

        // Fuera de la transacción: verificar pendientes y actualizar estado
        $estado = $this->actualizarEstadoReceta($receta, $creados);

        return ['creados' => $creados, 'estado' => $estado];
    }

    /**
     * Entrega un detalle consumiendo lotes en orden FIFO.
     * La cantidad de dosis se acumula en el detalle; el inventario se descuenta en su unidad base.
     */
    private function dispensarDetalle(
        Consulta $consulta,
        RecetasMedica $receta,
        DetalleRecetasMedica $detalle,
        float $cantidadDosis,
        float $cantidadInventario,
        ?string $observaciones,
        int $sedeId,
        int $usuarioId
    ): void {
        $nombreProducto = $detalle->producto->nombre_pdt ?? 'producto';

        $lotes = $this->lotesDisponiblesQuery($detalle->pdt_id, $sedeId)
            ->lockForUpdate()
            ->get();

        if ($lotes->isEmpty()) {
            throw StockInsuficienteException::sinStock($nombreProducto, $sedeId);
        }

        $restante = $cantidadInventario;

        foreach ($lotes as $lote) {
            if ($restante <= 0) {
                break;
            }

            $restante = round($restante - $this->descontarDeLote(
                $consulta,
                $receta,
                $detalle,
                $lote,
                $restante,
                $observaciones,
                $sedeId,
                $usuarioId
            ), 2);
        }

        if ($restante > 0) {
            throw StockInsuficienteException::faltante($nombreProducto, $restante);
        }

        $detalle->update([
            'cantidad_drm' => round((float) $detalle->cantidad_drm + $cantidadDosis, 2),
        ]);
    }

    /**
     * Descuenta de un lote lo que pueda (en unidad base) y registra dispensación + movimiento.
     *
     * @return float Cantidad base tomada de este lote (0 si no tenía stock en la sede)
     */
    private function descontarDeLote(
        Consulta $consulta,
        RecetasMedica $receta,
        DetalleRecetasMedica $detalle,
        Lote $lote,
        float $restante,
        ?string $observaciones,
        int $sedeId,
        int $usuarioId
    ): float {
        $existencia = InventarioSedeLote::where('lot_id', $lote->id_lot)
            ->where('sed_id', $sedeId)
            ->lockForUpdate()
            ->first();

        $disponible = round((float) ($existencia->cantidad_convertida_isl ?? 0), 2);
        $cantidad = round(min($disponible, $restante), 2);

        if ($cantidad <= 0) {
            return 0;
        }

        $unidadInventario = $detalle->producto->unm_id ?? $detalle->unm_id;

        $dispensacion = Dispensacion::create([
            'rme_id'                 => $receta->id_rme,
            'drm_id'                 => $detalle->id_drm,
            'pdt_id'                 => $detalle->pdt_id,
            'paciente_per_id'        => $consulta->paciente_per_id,
            'lot_id'                 => $lote->id_lot,
            'cantidad_dsm'           => $cantidad,
            'unm_id'                 => $unidadInventario,
            'sed_id'                 => $sedeId,
            'use_id'                 => $usuarioId,
            'fecha_dispensacion_dsm' => now()->toDateString(),
            'observaciones_dsm'      => $observaciones,
        ]);

        $this->inventario->salidaPorConvertida($lote->id_lot, $sedeId, $cantidad, [
            'unm_id'              => $unidadInventario,
            'referencia_type_moi' => 'Dispensacion',
            'referencia_id_moi'   => $dispensacion->id_dsm,
            'use_id'              => $usuarioId,
            'observacion_moi'     => 'Dispensación en consulta #' . $consulta->id_cme . ' · Lote ' . $lote->codigo_lote_lot
                . ' · ' . rtrim(rtrim(number_format((float) $detalle->cantidad_prescrita_drm, 2, '.', ''), '0'), '.')
                . ' ' . mb_strtolower((string) $detalle->unidad_prescrita_drm) . ' recetadas',
        ]);

        return $cantidad;
    }

    /**
     * Reglas:
     *   1) Dispensó algo + quedan pendientes    → ABIERTA (se puede completar después)
     *   2) Dispensó algo + no quedan pendientes → CERRADA (todo entregado)
     *   3) No dispensó nada                     → CERRADA (cerrada sin entregas)
     */
    private function actualizarEstadoReceta(RecetasMedica $receta, int $creados): int
    {
        $quedanPendientes = DetalleRecetasMedica::where('rme_id', $receta->id_rme)
            ->get()
            ->contains(fn($d) => $d->cantidad_pendiente > 0);

        $estado = ($creados > 0 && $quedanPendientes)
            ? Consulta::RECETA_ABIERTA
            : Consulta::RECETA_CERRADA;

        $receta->update(['estado_rme' => $estado]);

        return $estado;
    }

    public function mensajeResultado(array $resultado): string
    {
        if ($resultado['creados'] === 0) {
            return 'Atención finalizada sin entregas. La consulta ha sido cerrada.';
        }

        $mensaje = "Se registraron {$resultado['creados']} dispensación(es) y se descontó el inventario.";

        return $mensaje . ($resultado['estado'] === Consulta::RECETA_ABIERTA
            ? ' Quedan medicamentos pendientes por entregar.'
            : ' La atención ha sido finalizada.');
    }
}
