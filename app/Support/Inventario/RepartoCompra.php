<?php

namespace App\Support\Inventario;

use App\Models\Compra;
use App\Models\Sede;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RepartoCompra
{
    public function __construct(private ServicioInventario $inventario)
    {
    }

    public function sedes(): Collection
    {
        $principal = Sede::idPrincipal();

        if ($principal === null) {
            throw new RuntimeException('No hay una sede principal marcada. Pide al administrador del sistema que indique cuál sede hace las compras.');
        }

        $sedes = DB::table('sede')
            ->where('status_id', Sede::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->orderBy('nombre_sed')
            ->get(['id_sed', 'nombre_sed'])
            ->map(fn ($sede) => (object) [
                'id'        => (int) $sede->id_sed,
                'nombre'    => $sede->nombre_sed,
                'principal' => (int) $sede->id_sed === $principal,
            ]);

        if (! $sedes->contains('principal', true)) {
            throw new RuntimeException('La sede principal está inactiva. Actívala antes de recibir la compra.');
        }

        return $sedes->sortByDesc('principal')->values();
    }

    public function repartir(float $cantidad, float $convertida, array $sedeIds, int $principalId, bool $fraccionable): array
    {
        $sedeIds = array_values(array_unique(array_map('intval', $sedeIds)));

        if (! in_array($principalId, $sedeIds, true)) {
            $sedeIds[] = $principalId;
        }

        $total = count($sedeIds);
        $porSede = $fraccionable
            ? intdiv((int) round($cantidad * 100), $total) / 100
            : floor(round($cantidad, 2) / $total);
        $factor = $cantidad > 0 ? $convertida / $cantidad : 0.0;

        $partes = [];
        $asignada = 0.0;
        $asignadaConvertida = 0.0;

        foreach ($sedeIds as $sedeId) {
            if ($sedeId === $principalId) {
                continue;
            }

            $parteConvertida = round($porSede * $factor, 2);
            $partes[$sedeId] = ['cantidad' => round($porSede, 2), 'convertida' => $parteConvertida];
            $asignada += $porSede;
            $asignadaConvertida += $parteConvertida;
        }

        $partes[$principalId] = [
            'cantidad'   => round($cantidad - $asignada, 2),
            'convertida' => round($convertida - $asignadaConvertida, 2),
        ];

        return $partes;
    }

    public function calcular(Compra|int $compra): array
    {
        $compraId = $compra instanceof Compra ? (int) $compra->id_cmp : $compra;
        $sedes = $this->sedes();
        $principal = (int) $sedes->firstWhere('principal', true)->id;
        $ids = $sedes->pluck('id')->all();

        $lineas = $this->detalles($compraId)->map(function ($detalle) use ($ids, $principal) {
            $unidad = UnidadCompra::de((int) $detalle->pdt_id);
            [$cantidad, $convertida] = $this->cantidades($detalle, $unidad);

            return $this->linea($detalle, $unidad, $this->repartir($cantidad, $convertida, $ids, $principal, $unidad->fraccionable()));
        })->values()->all();

        return ['sedes' => $sedes->all(), 'principal' => $principal, 'lineas' => $lineas];
    }

    public function aplicar(Compra|int $compra): array
    {
        $compraId = $compra instanceof Compra ? (int) $compra->id_cmp : $compra;

        return DB::transaction(function () use ($compraId) {
            $registro = DB::table('compra')->where('id_cmp', $compraId)->whereNull('deleted_at')->lockForUpdate()->first();

            if (! $registro) {
                throw new RuntimeException('La compra no existe o fue eliminada.');
            }

            if ($registro->estado_compra_cmp === Compra::ESTADO_FINALIZADA) {
                throw new RuntimeException('Esta compra ya fue recibida y repartida. No se puede recibir dos veces.');
            }

            $reparto = $this->calcular($compraId);

            if ($reparto['lineas'] === []) {
                throw new RuntimeException('La compra no tiene productos. Agrega al menos uno antes de recibirla.');
            }

            foreach ($reparto['lineas'] as $linea) {
                if ($linea['lote_id'] <= 0) {
                    throw new RuntimeException("El producto {$linea['producto']} no tiene lote. Quítalo de la compra y agrégalo de nuevo.");
                }

                foreach ($linea['partes'] as $sedeId => $parte) {
                    if ($parte['cantidad'] <= 0) {
                        continue;
                    }

                    $this->inventario->entrada($linea['lote_id'], (int) $sedeId, $parte['cantidad'], $parte['convertida'], [
                        'unm_id'              => $linea['unm_id'],
                        'mod_id'              => $registro->mod_id,
                        'referencia_type_moi' => 'compra',
                        'referencia_id_moi'   => $registro->id_cmp,
                        'observacion_moi'     => 'Entrada por reparto de la compra #' . $registro->id_cmp,
                    ]);
                }
            }

            DB::table('compra')->where('id_cmp', $registro->id_cmp)->update([
                'estado_compra_cmp' => Compra::ESTADO_FINALIZADA,
                'updated_id'        => Auth::id(),
                'updated_at'        => now(),
            ]);

            return $reparto;
        });
    }

    public function aplicado(int $compraId): array
    {
        $movimientos = DB::table('movimiento_inventario')
            ->join('sede', 'sede.id_sed', '=', 'movimiento_inventario.sed_id')
            ->where('movimiento_inventario.referencia_type_moi', 'compra')
            ->where('movimiento_inventario.referencia_id_moi', $compraId)
            ->where('movimiento_inventario.tipo_movimiento_moi', ServicioInventario::ENTRADA)
            ->get([
                'movimiento_inventario.lot_id',
                'movimiento_inventario.sed_id',
                'movimiento_inventario.cantidad_moi',
                'movimiento_inventario.cantidad_convertida_moi',
                'sede.nombre_sed',
                'sede.es_principal_sed',
            ]);

        $sedes = $movimientos->unique('sed_id')
            ->map(fn ($mov) => (object) [
                'id'        => (int) $mov->sed_id,
                'nombre'    => $mov->nombre_sed,
                'principal' => (bool) $mov->es_principal_sed,
            ])
            ->sortBy('nombre')
            ->sortByDesc('principal')
            ->values();

        $lineas = $this->detalles($compraId)->map(function ($detalle) use ($movimientos) {
            $partes = [];

            foreach ($movimientos->where('lot_id', $detalle->lot_id) as $mov) {
                $sedeId = (int) $mov->sed_id;
                $partes[$sedeId] = [
                    'cantidad'   => round(($partes[$sedeId]['cantidad'] ?? 0) + (float) $mov->cantidad_moi, 2),
                    'convertida' => round(($partes[$sedeId]['convertida'] ?? 0) + (float) $mov->cantidad_convertida_moi, 2),
                ];
            }

            return $this->linea($detalle, UnidadCompra::de((int) $detalle->pdt_id), $partes);
        })->values()->all();

        $principal = $sedes->firstWhere('principal', true);

        return ['sedes' => $sedes->all(), 'principal' => $principal ? $principal->id : null, 'lineas' => $lineas];
    }

    private function detalles(int $compraId): Collection
    {
        return DB::table('detalle_compra')
            ->join('producto', 'producto.id_pdt', '=', 'detalle_compra.pdt_id')
            ->leftJoin('lote', 'lote.id_lot', '=', 'detalle_compra.lot_id')
            ->where('detalle_compra.cmp_id', $compraId)
            ->whereNull('detalle_compra.deleted_at')
            ->where('detalle_compra.cantidad_dcm', '>', 0)
            ->orderBy('detalle_compra.id_dcm')
            ->get([
                'detalle_compra.id_dcm',
                'detalle_compra.pdt_id',
                'detalle_compra.lot_id',
                'detalle_compra.unm_id',
                'detalle_compra.cantidad_dcm',
                'detalle_compra.cantidad_convertida_dcm',
                'producto.nombre_pdt',
                'lote.codigo_lote_lot',
            ]);
    }

    private function cantidades(object $detalle, UnidadCompra $unidad): array
    {
        $convertida = (float) $detalle->cantidad_convertida_dcm;

        return $unidad->inventarioEnUnidades()
            ? [$convertida, $convertida]
            : [(float) $detalle->cantidad_dcm, $convertida];
    }

    private function linea(object $detalle, UnidadCompra $unidad, array $partes): array
    {
        [$cantidad, $convertida] = $this->cantidades($detalle, $unidad);
        $enUnidades = $unidad->inventarioEnUnidades();
        $comprada = (float) $detalle->cantidad_dcm;

        return [
            'detalle_id'   => (int) $detalle->id_dcm,
            'lote_id'      => (int) $detalle->lot_id,
            'lote'         => $detalle->codigo_lote_lot,
            'producto_id'  => (int) $detalle->pdt_id,
            'producto'     => $detalle->nombre_pdt,
            'unm_id'       => $detalle->unm_id === null ? null : (int) $detalle->unm_id,
            'unidad'       => $enUnidades ? 'unidad' : $unidad->unidad,
            'unidad_plural' => $enUnidades ? 'unidades' : $unidad->unidadPlural,
            'unidad_base'  => $enUnidades ? 'unidades' : $unidad->unidadBase,
            'fraccionable' => $unidad->fraccionable(),
            'cantidad'     => $cantidad,
            'convertida'   => $convertida,
            'nota'         => $enUnidades && $unidad->modo === UnidadCompra::MODO_ENVASE
                ? 'Se compró ' . $unidad->resumen($comprada) . '; se reparte en unidades sueltas'
                : null,
            'partes'       => $partes,
        ];
    }
}
