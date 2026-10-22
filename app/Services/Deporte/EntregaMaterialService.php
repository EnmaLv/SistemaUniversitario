<?php

namespace App\Services\Deporte;

use App\Exceptions\Deporte\OperacionDeporteException;
use App\Models\Deporte\EntregaMaterial;
use App\Models\TipoProducto;
use App\Support\Auditoria\Bitacora;
use App\Support\Inventario\ServicioInventario;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EntregaMaterialService
{
    public const REFERENCIA = 'entrega_material';

    private const TOLERANCIA = 0.0001;

    public function __construct(private ServicioInventario $inventario)
    {
    }

    public function materialesDisponibles(int $sedeId): Collection
    {
        return TipoProducto::productosDeTipos([TipoProducto::MATERIAL_OFICINA])
            ->where('producto.status_id', 1)
            ->orderBy('producto.nombre_pdt')
            ->get(['producto.id_pdt', 'producto.codigo_pdt', 'producto.nombre_pdt'])
            ->map(function ($producto) use ($sedeId) {
                $producto->disponible = $this->disponible((int) $producto->id_pdt, $sedeId);

                return $producto;
            });
    }

    public function disponible(int $productoId, int $sedeId): float
    {
        return (float) $this->inventario->existenciasDisponibles($productoId, $sedeId)->sum('cantidad_isl');
    }

    public function registrar(array $datos, int $sedeId): EntregaMaterial
    {
        $personaId = null;
        $cedula = trim((string) ($datos['cedula'] ?? ''));
        $dependencia = trim((string) ($datos['dependencia'] ?? ''));

        if ($cedula !== '') {
            $persona = PrestamoService::buscarPersona($cedula);

            if (! $persona) {
                throw new OperacionDeporteException('No existe ninguna persona registrada con esa cédula.');
            }

            $personaId = (int) $persona->id_per;
        } elseif ($dependencia === '') {
            throw new OperacionDeporteException('Indique la cédula de la persona o la dependencia que recibe el material.');
        }

        $items = $this->agrupar($datos['items'] ?? []);

        if ($items === []) {
            throw new OperacionDeporteException('Agregue al menos un producto con una cantidad mayor que cero.');
        }

        $nombres = DB::table('producto')->whereIn('id_pdt', array_keys($items))->pluck('nombre_pdt', 'id_pdt');

        foreach (array_keys($items) as $productoId) {
            if (! TipoProducto::productoEsDeTipo($productoId, TipoProducto::MATERIAL_OFICINA)) {
                throw new OperacionDeporteException('Solo se pueden entregar productos de tipo Material de oficina.');
            }
        }

        $entrega = DB::transaction(function () use ($items, $personaId, $dependencia, $datos, $sedeId, $nombres) {
            $entrega = EntregaMaterial::create([
                'fecha_ema'       => now(),
                'per_id'          => $personaId,
                'dependencia_ema' => $personaId === null ? $dependencia : ($dependencia ?: null),
                'motivo_ema'      => trim((string) $datos['motivo']),
                'sed_id'          => $sedeId,
                'use_id'          => Auth::id(),
            ]);

            foreach ($items as $productoId => $cantidad) {
                $this->descontar($productoId, $sedeId, $cantidad, $entrega->id_ema, (string) ($nombres[$productoId] ?? 'producto'));

                $entrega->detalles()->create([
                    'pdt_id'       => $productoId,
                    'cantidad_dem' => $cantidad,
                ]);
            }

            return $entrega;
        });

        Bitacora::registrar(
            accion: 'entrega_material_registrada',
            modulo: 'deporte',
            tablaAfectada: 'entrega_material',
            registroId: $entrega->id_ema,
            datosModificados: [
                'per_id'    => $personaId,
                'sed_id'    => $sedeId,
                'productos' => $items,
            ],
        );

        return $entrega;
    }

    private function descontar(int $productoId, int $sedeId, float $cantidad, int $entregaId, string $nombre): void
    {
        $existencias = $this->inventario->existenciasDisponibles($productoId, $sedeId);
        $disponible = (float) $existencias->sum('cantidad_isl');

        if ($disponible + self::TOLERANCIA < $cantidad) {
            throw new OperacionDeporteException(sprintf(
                'No hay existencia suficiente de %s en la sede: disponible %s, solicitado %s.',
                $nombre,
                $this->formato($disponible),
                $this->formato($cantidad)
            ));
        }

        $restante = $cantidad;

        foreach ($existencias as $existencia) {
            if ($restante <= self::TOLERANCIA) {
                break;
            }

            $tomar = min((float) $existencia->cantidad_isl, $restante);

            if ($tomar <= 0) {
                continue;
            }

            $this->inventario->salida((int) $existencia->lot_id, $sedeId, $tomar, null, [
                'mod_id'              => PrestamoService::moduloDeporteId(),
                'referencia_type_moi' => self::REFERENCIA,
                'referencia_id_moi'   => $entregaId,
                'observacion_moi'     => 'Entrega de material #' . $entregaId,
            ]);

            $restante -= $tomar;
        }
    }

    private function agrupar(array $items): array
    {
        $agrupados = [];

        foreach ($items as $item) {
            $productoId = (int) ($item['pdt_id'] ?? 0);
            $cantidad = round((float) ($item['cantidad'] ?? 0), 2);

            if ($productoId <= 0 || $cantidad <= 0) {
                continue;
            }

            $agrupados[$productoId] = ($agrupados[$productoId] ?? 0) + $cantidad;
        }

        return $agrupados;
    }

    private function formato(float $valor): string
    {
        return rtrim(rtrim(number_format($valor, 2, ',', '.'), '0'), ',');
    }
}
