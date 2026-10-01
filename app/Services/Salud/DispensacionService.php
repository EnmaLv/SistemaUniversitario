<?php

namespace App\Services\Salud;

use App\Exceptions\Salud\StockInsuficienteException;
use App\Models\InventarioSedeLote;
use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\Unidad;
use App\Models\salud\Consulta;
use App\Models\salud\DetalleRecetasMedica;
use App\Models\salud\Dispensacion;
use App\Models\salud\RecetasMedica;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Paso 3: dispensación de medicamentos con descuento FIFO de inventario.
 */
class DispensacionService
{
    public const SEDE_POR_DEFECTO = 1;

    /* ──────────────────────────────────────────────────────────────
     |  Utilidades
     ────────────────────────────────────────────────────────────── */

    public static function sedeDelUsuario($usuario): int
    {
        return (int) ($usuario?->persona?->sede_id ?? self::SEDE_POR_DEFECTO);
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
        return Lote::where('producto_id', $productoId)
            ->where(function ($q) {
                $q->whereNull('fecha_vencimiento')
                    ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
            })
            ->whereHas('inventarioSedeLotes', function ($q) use ($sedeId) {
                $q->where('sede_id', $sedeId)->where('cantidad', '>', 0);
            })
            ->orderByRaw('fecha_vencimiento IS NULL ASC')
            ->orderBy('fecha_vencimiento', 'asc')
            ->orderBy('id', 'asc');
    }

    /* ──────────────────────────────────────────────────────────────
     |  Vista
     ────────────────────────────────────────────────────────────── */

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

            $lotesPorDetalle[$detalle->id] = $this->lotesDisponiblesQuery($detalle->producto_id, $sedeId)
                ->with(['inventarioSedeLotes' => fn($q) => $q->where('sede_id', $sedeId)])
                ->get();
        }

        return $lotesPorDetalle;
    }

    /* ──────────────────────────────────────────────────────────────
     |  Registro
     ────────────────────────────────────────────────────────────── */

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
            $detalles = DetalleRecetasMedica::with('producto')
                ->where('receta_id', $receta->id)
                ->lockForUpdate()
                ->get();

            foreach ($detalles as $detalle) {
                $item          = $items[$detalle->id] ?? [];
                $cantidadInput = (float) ($item['cantidad'] ?? 0);

                if (!self::marcadoParaDispensar($item) || $cantidadInput <= 0) {
                    continue;
                }

                $cantidad = min($cantidadInput, $detalle->cantidad_pendiente);

                if ($cantidad <= 0) {
                    continue;
                }

                $this->dispensarDetalle(
                    $consulta,
                    $receta,
                    $detalle,
                    $cantidad,
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
     */
    private function dispensarDetalle(
        Consulta $consulta,
        RecetasMedica $receta,
        DetalleRecetasMedica $detalle,
        float $cantidad,
        ?string $observaciones,
        int $sedeId,
        int $usuarioId
    ): void {
        $nombreProducto = $detalle->producto->nombre ?? 'producto';

        $lotes = $this->lotesDisponiblesQuery($detalle->producto_id, $sedeId)
            ->where('cantidad_actual', '>', 0)
            ->lockForUpdate()
            ->get();

        if ($lotes->isEmpty()) {
            throw StockInsuficienteException::sinStock($nombreProducto, $sedeId);
        }

        $factor    = (float) (Unidad::find($detalle->unidad_id)?->factor_a_gramo ?? 1);
        $restante  = $cantidad;
        $entregado = 0;

        foreach ($lotes as $lote) {
            if ($restante <= 0) {
                break;
            }

            $tomado = $this->descontarDeLote(
                $consulta,
                $receta,
                $detalle,
                $lote,
                $restante,
                $factor,
                $observaciones,
                $sedeId,
                $usuarioId
            );

            $entregado += $tomado;
            $restante  -= $tomado;
        }

        if ($restante > 0) {
            throw StockInsuficienteException::faltante($nombreProducto, $restante);
        }

        $detalle->update([
            'cantidad' => (float) $detalle->cantidad + $entregado,
        ]);
    }

    /**
     * Descuenta de un lote lo que pueda y registra dispensación + movimiento.
     *
     * @return float Cantidad tomada de este lote (0 si no tenía stock en la sede)
     */
    private function descontarDeLote(
        Consulta $consulta,
        RecetasMedica $receta,
        DetalleRecetasMedica $detalle,
        Lote $lote,
        float $restante,
        float $factor,
        ?string $observaciones,
        int $sedeId,
        int $usuarioId
    ): float {
        $inventario = InventarioSedeLote::where('lote_id', $lote->id)
            ->where('sede_id', $sedeId)
            ->lockForUpdate()
            ->first();

        if (!$inventario || (float) $inventario->cantidad <= 0) {
            return 0;
        }

        $cantidad = min((float) $inventario->cantidad, $restante);

        if ($cantidad <= 0) {
            return 0;
        }

        $cantidadConvertida = round($cantidad * $factor, 2);
        $cantAntes          = (float) $inventario->cantidad;
        $cantDespues        = $cantAntes - $cantidad;

        $inventario->update([
            'cantidad'            => $cantDespues,
            'cantidad_convertida' => max(0, (float) $inventario->cantidad_convertida - $cantidadConvertida),
        ]);

        $lote->update([
            'cantidad_actual' => max(0, (float) $lote->cantidad_actual - $cantidad),
        ]);

        Dispensacion::create([
            'receta_medica_id'         => $receta->id,
            'detalle_receta_medica_id' => $detalle->id,
            'producto_id'              => $detalle->producto_id,
            'id_persona'               => $consulta->id_persona,
            'lote_id'                  => $lote->id,
            'cantidad'                 => $cantidad,
            'unidad_id'                => $detalle->unidad_id,
            'sede_id'                  => $sedeId,
            'usuario_id'               => $usuarioId,
            'fecha'                    => now()->toDateString(),
            'observaciones'            => $observaciones,
        ]);

        MovimientoInventario::create([
            'producto_id'         => $detalle->producto_id,
            'lote_id'             => $lote->id,
            'sede_id'             => $sedeId,
            'modulo_origen_id'    => null,
            'tipo_movimiento'     => 'SALIDA',
            'unidad_id'           => $detalle->unidad_id,
            'cantidad'            => $cantidad,
            'cantidad_convertida' => $cantidadConvertida,
            'cantidad_anterior'   => $cantAntes,
            'cantidad_final'      => $cantDespues,
            'referencia_type'     => 'Dispensacion',
            'fecha'               => now()->toDateString(),
            'observaciones'       => 'Dispensación en consulta #' . $consulta->id . ' · Lote ' . $lote->codigo_lote,
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
        $quedanPendientes = DetalleRecetasMedica::where('receta_id', $receta->id)
            ->get()
            ->contains(fn($d) => $d->cantidad_pendiente > 0);

        $estado = ($creados > 0 && $quedanPendientes)
            ? Consulta::RECETA_ABIERTA
            : Consulta::RECETA_CERRADA;

        $receta->update(['estado' => $estado]);

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
