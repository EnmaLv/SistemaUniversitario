<?php

namespace App\Support\Inventario;

use App\Exceptions\Salud\StockInsuficienteException;
use App\Models\MovimientoInventario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ServicioInventario
{
    public const ENTRADA = 'ENTRADA';
    public const SALIDA = 'SALIDA';
    public const MERMA = 'MERMA';
    public const AJUSTE = 'AJUSTE';
    public const TRANSFERENCIA_SALIDA = 'TRANSFERENCIA_SALIDA';
    public const TRANSFERENCIA_ENTRADA = 'TRANSFERENCIA_ENTRADA';

    private const TOLERANCIA = 0.0001;

    public function entrada(int $loteId, int $sedeId, float $cantidad, ?float $cantidadConvertida = null, array $datos = []): MovimientoInventario
    {
        $this->exigirPositiva($cantidad);

        return DB::transaction(function () use ($loteId, $sedeId, $cantidad, $cantidadConvertida, $datos) {
            $lote = $this->bloquearLote($loteId);
            $existencia = $this->bloquearExistencia($loteId, $sedeId, true);
            $convertida = $cantidadConvertida ?? $cantidad * $this->factor($lote, $existencia);

            return $this->aplicar($lote, $existencia, $cantidad, $convertida, $datos['tipo'] ?? self::ENTRADA, $datos);
        });
    }

    public function salida(int $loteId, int $sedeId, float $cantidad, ?float $cantidadConvertida = null, array $datos = []): MovimientoInventario
    {
        $this->exigirPositiva($cantidad);

        return DB::transaction(function () use ($loteId, $sedeId, $cantidad, $cantidadConvertida, $datos) {
            $lote = $this->bloquearLote($loteId);
            $existencia = $this->bloquearExistencia($loteId, $sedeId, false);
            $disponible = (float) ($existencia->cantidad_isl ?? 0);

            if (! $existencia || $disponible + self::TOLERANCIA < $cantidad) {
                throw new StockInsuficienteException(sprintf(
                    'Stock insuficiente del lote %s en la sede: disponible %s, solicitado %s.',
                    $lote->codigo_lote_lot,
                    $this->formato($disponible),
                    $this->formato($cantidad)
                ));
            }

            $convertida = $cantidadConvertida ?? $cantidad * $this->factor($lote, $existencia);
            $convertida = min($convertida, (float) $existencia->cantidad_convertida_isl);

            return $this->aplicar($lote, $existencia, -$cantidad, -$convertida, $datos['tipo'] ?? self::SALIDA, $datos);
        });
    }

    public function salidaPorConvertida(int $loteId, int $sedeId, float $cantidadConvertida, array $datos = []): MovimientoInventario
    {
        $this->exigirPositiva($cantidadConvertida);

        return DB::transaction(function () use ($loteId, $sedeId, $cantidadConvertida, $datos) {
            $lote = $this->bloquearLote($loteId);
            $existencia = $this->bloquearExistencia($loteId, $sedeId, false);
            $disponible = (float) ($existencia->cantidad_convertida_isl ?? 0);

            if (! $existencia || $disponible + self::TOLERANCIA < $cantidadConvertida) {
                throw new StockInsuficienteException(sprintf(
                    'Stock insuficiente del lote %s en la sede: disponible %s, solicitado %s (unidad base).',
                    $lote->codigo_lote_lot,
                    $this->formato($disponible),
                    $this->formato($cantidadConvertida)
                ));
            }

            $factor = $this->factor($lote, $existencia);
            $cantidad = min((float) $existencia->cantidad_isl, $factor > 0 ? $cantidadConvertida / $factor : $cantidadConvertida);

            return $this->aplicar($lote, $existencia, -$cantidad, -$cantidadConvertida, $datos['tipo'] ?? self::SALIDA, $datos);
        });
    }

    public function ajuste(int $loteId, int $sedeId, float $nuevaCantidad, ?float $nuevaConvertida = null, array $datos = []): MovimientoInventario
    {
        if ($nuevaCantidad < 0) {
            throw new InvalidArgumentException('La existencia ajustada no puede ser negativa.');
        }

        return DB::transaction(function () use ($loteId, $sedeId, $nuevaCantidad, $nuevaConvertida, $datos) {
            $lote = $this->bloquearLote($loteId);
            $existencia = $this->bloquearExistencia($loteId, $sedeId, true);
            $convertida = $nuevaConvertida ?? $nuevaCantidad * $this->factor($lote, $existencia);

            return $this->aplicar(
                $lote,
                $existencia,
                $nuevaCantidad - (float) $existencia->cantidad_isl,
                $convertida - (float) $existencia->cantidad_convertida_isl,
                self::AJUSTE,
                $datos
            );
        });
    }

    public function transferir(int $loteId, int $sedeOrigen, int $sedeDestino, float $cantidad, array $datos = []): array
    {
        if ($sedeOrigen === $sedeDestino) {
            throw new InvalidArgumentException('La sede de origen y la de destino deben ser distintas.');
        }

        return DB::transaction(function () use ($loteId, $sedeOrigen, $sedeDestino, $cantidad, $datos) {
            $salida = $this->salida($loteId, $sedeOrigen, $cantidad, null, array_merge($datos, ['tipo' => self::TRANSFERENCIA_SALIDA]));

            $entrada = $this->entrada(
                $loteId,
                $sedeDestino,
                $cantidad,
                (float) $salida->cantidad_convertida_moi,
                array_merge($datos, ['tipo' => self::TRANSFERENCIA_ENTRADA])
            );

            return [$salida, $entrada];
        });
    }

    public function recalcular(?int $productoId = null, ?int $sedeId = null): int
    {
        return DB::transaction(function () use ($productoId, $sedeId) {
            $borrar = DB::table('inventario_total');
            $productoId !== null && $borrar->where('pdt_id', $productoId);
            $sedeId !== null && $borrar->where('sed_id', $sedeId);
            $borrar->delete();

            $filas = DB::table('inventario_sede_lote as existencia')
                ->join('lote', 'lote.id_lot', '=', 'existencia.lot_id')
                ->when($productoId !== null, fn ($consulta) => $consulta->where('lote.pdt_id', $productoId))
                ->when($sedeId !== null, fn ($consulta) => $consulta->where('existencia.sed_id', $sedeId))
                ->groupBy('lote.pdt_id', 'existencia.sed_id')
                ->selectRaw('lote.pdt_id, existencia.sed_id, SUM(existencia.cantidad_isl) AS cantidad_total, SUM(existencia.cantidad_convertida_isl) AS cantidad_convertida_total')
                ->get();

            $ahora = now();
            $registros = $filas->map(fn ($fila) => [
                'pdt_id'                        => $fila->pdt_id,
                'sed_id'                        => $fila->sed_id,
                'cantidad_total_int'            => round((float) $fila->cantidad_total, 2),
                'cantidad_convertida_total_int' => round((float) $fila->cantidad_convertida_total, 2),
                'created_at'                => $ahora,
                'updated_at'                => $ahora,
            ])->all();

            foreach (array_chunk($registros, 500) as $lote) {
                DB::table('inventario_total')->insert($lote);
            }

            return count($registros);
        });
    }

    public function existenciasDisponibles(int $productoId, int $sedeId)
    {
        return DB::table('inventario_sede_lote as existencia')
            ->join('lote', 'lote.id_lot', '=', 'existencia.lot_id')
            ->where('lote.pdt_id', $productoId)
            ->where('existencia.sed_id', $sedeId)
            ->where('lote.status_id', 1)
            ->whereNull('lote.deleted_at')
            ->where('existencia.cantidad_convertida_isl', '>', 0)
            ->where(fn ($consulta) => $consulta->whereNull('lote.fecha_vencimiento_lot')->orWhereDate('lote.fecha_vencimiento_lot', '>=', now()->toDateString()))
            ->orderByRaw('CASE WHEN lote.fecha_vencimiento_lot IS NULL THEN 1 ELSE 0 END')
            ->orderBy('lote.fecha_vencimiento_lot')
            ->orderBy('lote.id_lot')
            ->select('existencia.*', 'lote.codigo_lote_lot', 'lote.fecha_vencimiento_lot')
            ->get();
    }

    private function aplicar(object $lote, object $existencia, float $deltaCantidad, float $deltaConvertida, string $tipo, array $datos): MovimientoInventario
    {
        $anterior = (float) $existencia->cantidad_isl;
        $final = round($anterior + $deltaCantidad, 2);
        $convertidaFinal = round(max(0, (float) $existencia->cantidad_convertida_isl + $deltaConvertida), 2);

        DB::table('inventario_sede_lote')->where('id_isl', $existencia->id_isl)->update([
            'cantidad_isl'            => max(0, $final),
            'cantidad_convertida_isl' => $convertidaFinal,
            'updated_id'              => Auth::id(),
            'updated_at'              => now(),
        ]);

        DB::table('lote')->where('id_lot', $lote->id_lot)->update([
            'cantidad_actual_lot' => (float) DB::table('inventario_sede_lote')->where('lot_id', $lote->id_lot)->sum('cantidad_convertida_isl'),
            'updated_at'          => now(),
        ]);

        $this->actualizarTotal((int) $lote->pdt_id, (int) $existencia->sed_id);

        return MovimientoInventario::todasLasSedes()->create([
            'pdt_id'                  => $lote->pdt_id,
            'lot_id'                  => $lote->id_lot,
            'sed_id'                  => $existencia->sed_id,
            'tipo_movimiento_moi'     => $tipo,
            'mod_id'                  => $datos['mod_id'] ?? null,
            'cantidad_moi'            => round(abs($deltaCantidad), 2),
            'cantidad_convertida_moi' => round(abs($deltaConvertida), 2),
            'cantidad_anterior_moi'   => $anterior,
            'cantidad_final_moi'      => max(0, $final),
            'referencia_type_moi'     => $datos['referencia_type_moi'] ?? null,
            'referencia_id_moi'       => $datos['referencia_id_moi'] ?? null,
            'unm_id'                  => $datos['unm_id'] ?? $this->unidadDelProducto((int) $lote->pdt_id),
            'use_id'                  => $datos['use_id'] ?? Auth::id(),
            'fecha_movimiento_moi'    => now(),
            'observacion_moi'         => $datos['observacion_moi'] ?? null,
        ]);
    }

    private function actualizarTotal(int $productoId, int $sedeId): void
    {
        $suma = DB::table('inventario_sede_lote as existencia')
            ->join('lote', 'lote.id_lot', '=', 'existencia.lot_id')
            ->where('lote.pdt_id', $productoId)
            ->where('existencia.sed_id', $sedeId)
            ->selectRaw('COALESCE(SUM(existencia.cantidad_isl), 0) AS cantidad, COALESCE(SUM(existencia.cantidad_convertida_isl), 0) AS convertida')
            ->first();

        DB::table('inventario_total')->upsert(
            [[
                'pdt_id'                        => $productoId,
                'sed_id'                        => $sedeId,
                'cantidad_total_int'            => round((float) $suma->cantidad, 2),
                'cantidad_convertida_total_int' => round((float) $suma->convertida, 2),
                'created_at'                    => now(),
                'updated_at'                    => now(),
            ]],
            ['pdt_id', 'sed_id'],
            ['cantidad_total_int', 'cantidad_convertida_total_int', 'updated_at']
        );
    }

    private function bloquearLote(int $loteId): object
    {
        $lote = DB::table('lote')->where('id_lot', $loteId)->lockForUpdate()->first();

        if (! $lote) {
            throw new InvalidArgumentException("El lote {$loteId} no existe.");
        }

        return $lote;
    }

    private function bloquearExistencia(int $loteId, int $sedeId, bool $crear): ?object
    {
        $existencia = DB::table('inventario_sede_lote')
            ->where('lot_id', $loteId)
            ->where('sed_id', $sedeId)
            ->lockForUpdate()
            ->first();

        if ($existencia || ! $crear) {
            return $existencia;
        }

        DB::table('inventario_sede_lote')->insertOrIgnore([
            'lot_id'                  => $loteId,
            'sed_id'                  => $sedeId,
            'cantidad_isl'            => 0,
            'cantidad_convertida_isl' => 0,
            'created_id'              => Auth::id(),
            'created_at'              => now(),
            'updated_at'              => now(),
        ]);

        return DB::table('inventario_sede_lote')
            ->where('lot_id', $loteId)
            ->where('sed_id', $sedeId)
            ->lockForUpdate()
            ->first();
    }

    private function factor(object $lote, ?object $existencia): float
    {
        if ($existencia && (float) $existencia->cantidad_isl > 0) {
            return (float) $existencia->cantidad_convertida_isl / (float) $existencia->cantidad_isl;
        }

        $producto = DB::table('producto')->where('id_pdt', $lote->pdt_id)->first(['peso_contenido_pdt']);

        return (float) ($producto->peso_contenido_pdt ?? 0) > 0 ? (float) $producto->peso_contenido_pdt : 1.0;
    }

    private function unidadDelProducto(int $productoId): ?int
    {
        return DB::table('producto')->where('id_pdt', $productoId)->value('unm_id');
    }

    private function exigirPositiva(float $cantidad): void
    {
        if ($cantidad <= 0) {
            throw new InvalidArgumentException('La cantidad del movimiento debe ser mayor que cero.');
        }
    }

    private function formato(float $valor): string
    {
        return rtrim(rtrim(number_format($valor, 2, ',', '.'), '0'), ',');
    }
}
