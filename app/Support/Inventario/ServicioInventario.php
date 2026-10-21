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
            $disponible = (float) ($existencia->cantidad ?? 0);

            if (! $existencia || $disponible + self::TOLERANCIA < $cantidad) {
                throw new StockInsuficienteException(sprintf(
                    'Stock insuficiente del lote %s en la sede: disponible %s, solicitado %s.',
                    $lote->codigo_lote,
                    $this->formato($disponible),
                    $this->formato($cantidad)
                ));
            }

            $convertida = $cantidadConvertida ?? $cantidad * $this->factor($lote, $existencia);
            $convertida = min($convertida, (float) $existencia->cantidad_convertida);

            return $this->aplicar($lote, $existencia, -$cantidad, -$convertida, $datos['tipo'] ?? self::SALIDA, $datos);
        });
    }

    public function salidaPorConvertida(int $loteId, int $sedeId, float $cantidadConvertida, array $datos = []): MovimientoInventario
    {
        $this->exigirPositiva($cantidadConvertida);

        return DB::transaction(function () use ($loteId, $sedeId, $cantidadConvertida, $datos) {
            $lote = $this->bloquearLote($loteId);
            $existencia = $this->bloquearExistencia($loteId, $sedeId, false);
            $disponible = (float) ($existencia->cantidad_convertida ?? 0);

            if (! $existencia || $disponible + self::TOLERANCIA < $cantidadConvertida) {
                throw new StockInsuficienteException(sprintf(
                    'Stock insuficiente del lote %s en la sede: disponible %s, solicitado %s (unidad base).',
                    $lote->codigo_lote,
                    $this->formato($disponible),
                    $this->formato($cantidadConvertida)
                ));
            }

            $factor = $this->factor($lote, $existencia);
            $cantidad = min((float) $existencia->cantidad, $factor > 0 ? $cantidadConvertida / $factor : $cantidadConvertida);

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
                $nuevaCantidad - (float) $existencia->cantidad,
                $convertida - (float) $existencia->cantidad_convertida,
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
                (float) $salida->cantidad_convertida,
                array_merge($datos, ['tipo' => self::TRANSFERENCIA_ENTRADA])
            );

            return [$salida, $entrada];
        });
    }

    public function recalcular(?int $productoId = null, ?int $sedeId = null): int
    {
        return DB::transaction(function () use ($productoId, $sedeId) {
            $borrar = DB::table('inventario_totals');
            $productoId !== null && $borrar->where('producto_id', $productoId);
            $sedeId !== null && $borrar->where('sede_id', $sedeId);
            $borrar->delete();

            $filas = DB::table('inventario_sede_lotes as existencia')
                ->join('lotes', 'lotes.id', '=', 'existencia.lote_id')
                ->when($productoId !== null, fn ($consulta) => $consulta->where('lotes.producto_id', $productoId))
                ->when($sedeId !== null, fn ($consulta) => $consulta->where('existencia.sede_id', $sedeId))
                ->groupBy('lotes.producto_id', 'existencia.sede_id')
                ->selectRaw('lotes.producto_id, existencia.sede_id, SUM(existencia.cantidad) AS cantidad_total, SUM(existencia.cantidad_convertida) AS cantidad_convertida_total')
                ->get();

            $ahora = now();
            $registros = $filas->map(fn ($fila) => [
                'producto_id'               => $fila->producto_id,
                'sede_id'                   => $fila->sede_id,
                'cantidad_total'            => round((float) $fila->cantidad_total, 2),
                'cantidad_convertida_total' => round((float) $fila->cantidad_convertida_total, 2),
                'created_at'                => $ahora,
                'updated_at'                => $ahora,
            ])->all();

            foreach (array_chunk($registros, 500) as $lote) {
                DB::table('inventario_totals')->insert($lote);
            }

            return count($registros);
        });
    }

    public function existenciasDisponibles(int $productoId, int $sedeId)
    {
        return DB::table('inventario_sede_lotes as existencia')
            ->join('lotes', 'lotes.id', '=', 'existencia.lote_id')
            ->where('lotes.producto_id', $productoId)
            ->where('existencia.sede_id', $sedeId)
            ->where('lotes.estado', 1)
            ->where('existencia.cantidad_convertida', '>', 0)
            ->where(fn ($consulta) => $consulta->whereNull('lotes.fecha_vencimiento')->orWhereDate('lotes.fecha_vencimiento', '>=', now()->toDateString()))
            ->orderByRaw('CASE WHEN lotes.fecha_vencimiento IS NULL THEN 1 ELSE 0 END')
            ->orderBy('lotes.fecha_vencimiento')
            ->orderBy('lotes.id')
            ->select('existencia.*', 'lotes.codigo_lote', 'lotes.fecha_vencimiento')
            ->get();
    }

    private function aplicar(object $lote, object $existencia, float $deltaCantidad, float $deltaConvertida, string $tipo, array $datos): MovimientoInventario
    {
        $anterior = (float) $existencia->cantidad;
        $final = round($anterior + $deltaCantidad, 2);
        $convertidaFinal = round(max(0, (float) $existencia->cantidad_convertida + $deltaConvertida), 2);

        DB::table('inventario_sede_lotes')->where('id', $existencia->id)->update([
            'cantidad'            => max(0, $final),
            'cantidad_convertida' => $convertidaFinal,
            'updated_at'          => now(),
        ]);

        DB::table('lotes')->where('id', $lote->id)->update([
            'cantidad_actual' => (float) DB::table('inventario_sede_lotes')->where('lote_id', $lote->id)->sum('cantidad_convertida'),
            'updated_at'      => now(),
        ]);

        $this->actualizarTotal((int) $lote->producto_id, (int) $existencia->sede_id);

        return MovimientoInventario::todasLasSedes()->create([
            'producto_id'         => $lote->producto_id,
            'lote_id'             => $lote->id,
            'sede_id'             => $existencia->sede_id,
            'tipo_movimiento'     => $tipo,
            'modulo_origen_id'    => $datos['modulo_origen_id'] ?? null,
            'cantidad'            => round(abs($deltaCantidad), 2),
            'cantidad_convertida' => round(abs($deltaConvertida), 2),
            'cantidad_anterior'   => $anterior,
            'cantidad_final'      => max(0, $final),
            'referencia_type'     => $datos['referencia_type'] ?? null,
            'referencia_id'       => $datos['referencia_id'] ?? null,
            'unidad_id'           => $datos['unidad_id'] ?? $this->unidadDelProducto((int) $lote->producto_id),
            'usuario_id'          => $datos['usuario_id'] ?? Auth::id(),
            'fecha'               => now(),
            'observaciones'       => $datos['observaciones'] ?? null,
        ]);
    }

    private function actualizarTotal(int $productoId, int $sedeId): void
    {
        $suma = DB::table('inventario_sede_lotes as existencia')
            ->join('lotes', 'lotes.id', '=', 'existencia.lote_id')
            ->where('lotes.producto_id', $productoId)
            ->where('existencia.sede_id', $sedeId)
            ->selectRaw('COALESCE(SUM(existencia.cantidad), 0) AS cantidad, COALESCE(SUM(existencia.cantidad_convertida), 0) AS convertida')
            ->first();

        DB::table('inventario_totals')->upsert(
            [[
                'producto_id'               => $productoId,
                'sede_id'                   => $sedeId,
                'cantidad_total'            => round((float) $suma->cantidad, 2),
                'cantidad_convertida_total' => round((float) $suma->convertida, 2),
                'created_at'                => now(),
                'updated_at'                => now(),
            ]],
            ['producto_id', 'sede_id'],
            ['cantidad_total', 'cantidad_convertida_total', 'updated_at']
        );
    }

    private function bloquearLote(int $loteId): object
    {
        $lote = DB::table('lotes')->where('id', $loteId)->lockForUpdate()->first();

        if (! $lote) {
            throw new InvalidArgumentException("El lote {$loteId} no existe.");
        }

        return $lote;
    }

    private function bloquearExistencia(int $loteId, int $sedeId, bool $crear): ?object
    {
        $existencia = DB::table('inventario_sede_lotes')
            ->where('lote_id', $loteId)
            ->where('sede_id', $sedeId)
            ->lockForUpdate()
            ->first();

        if ($existencia || ! $crear) {
            return $existencia;
        }

        DB::table('inventario_sede_lotes')->insertOrIgnore([
            'lote_id'             => $loteId,
            'sede_id'             => $sedeId,
            'cantidad'            => 0,
            'cantidad_convertida' => 0,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        return DB::table('inventario_sede_lotes')
            ->where('lote_id', $loteId)
            ->where('sede_id', $sedeId)
            ->lockForUpdate()
            ->first();
    }

    private function factor(object $lote, ?object $existencia): float
    {
        if ($existencia && (float) $existencia->cantidad > 0) {
            return (float) $existencia->cantidad_convertida / (float) $existencia->cantidad;
        }

        $producto = DB::table('productos')->where('id', $lote->producto_id)->first(['peso_contenido']);

        return (float) ($producto->peso_contenido ?? 0) > 0 ? (float) $producto->peso_contenido : 1.0;
    }

    private function unidadDelProducto(int $productoId): ?int
    {
        return DB::table('productos')->where('id', $productoId)->value('unidad_id');
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
