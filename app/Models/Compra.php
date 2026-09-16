<?php

namespace App\Models;

use App\Traits\ConvierteAMayusculasNoEloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\FiltroPorSede;

class Compra extends Model
{
    use ConvierteAMayusculasNoEloquent;
    protected $table = 'compras';

    protected $fillable = [
        'proveedor_id',
        'fecha',
        'total',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
        'total' => 'decimal:2',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function detalleCompras()
    {
        return $this->hasMany(DetalleCompra::class);
    }

    public function getDatosFormulario()
    {
        return [
            'proveedores' => DB::table('proveedors')
                ->select('id', 'nombre', 'email')
                ->where('estado', 1)
                ->orderBy('nombre')
                ->get(),

            'productos' => DB::table('productos')
                ->select('id', 'codigo', 'nombre')
                ->where('estado', 1)
                ->orderBy('nombre')
                ->get(),

            'sedes' => DB::table('sede')
                ->select('id', 'nombre')
                ->where('activo', 1)
                ->orderBy('nombre')
                ->get(),
        ];
    }

    public static function listarCompras($buscar = null, $estado = null, $fechas = null)
    {
        $query = DB::table('compras')
            ->join('proveedors', 'proveedors.id', '=', 'compras.proveedor_id')
            ->select(
                'compras.*',
                'proveedors.empresa as proveedor_empresa',
                'proveedors.nombre as proveedor_nombre',
                'proveedors.id as proveedor_id'
            );

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('proveedors.empresa', 'like', "%{$buscar}%")
                    ->orWhere('proveedors.nombre', 'like', "%{$buscar}%")
                    ->orWhere('compras.id', 'like', "%{$buscar}%");
            });
        }

        if ($estado !== null && $estado !== '') {
            $query->where('compras.estado', $estado);
        }

        if ($fechas !== null) {
            $query->whereBetween('compras.fecha', [$fechas['fecha_desde'], $fechas['fecha_hasta']]);
        }

        return $query->orderBy('compras.id', 'desc')->paginate(10)->withQueryString();
    }

    public static function crearCompra(array $data)
    {
        $helper = new self();

        $data = $helper->convertirCamposAMayusculas($data, ['observaciones']);

        return DB::table('compras')->insertGetId([
            'proveedor_id'  => $data['proveedor_id'],
            'fecha'         => $data['fecha'],
            'observaciones' => $data['observaciones'] ?? null,
            'total'         => 0,
            'estado'        => 'Pendiente',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    public static function obtenerDetallesCompra($compra_id)
    {
        return DB::table('detalle_compras')
            ->join('productos', 'productos.id', '=', 'detalle_compras.producto_id')
            ->join('lotes', 'lotes.id', '=', 'detalle_compras.lote_id')
            ->join('unidades', 'unidades.id', '=', 'detalle_compras.unidad_id')
            ->where('detalle_compras.compra_id', $compra_id)
            ->select(
                'detalle_compras.*',
                'productos.nombre as producto_nombre',
                'productos.codigo as producto_codigo',
                'lotes.codigo_lote',
                'lotes.fecha_vencimiento',
                'unidades.nombre as unidad_nombre',
                'unidades.abreviatura as unidad_abreviatura'
            )
            ->orderBy('detalle_compras.id')
            ->get();
    }

    public static function obtenerSedeDestino($compra_id)
    {
        return DB::table('movimiento_inventarios')
            ->join('sede', 'sede.id', '=', 'movimiento_inventarios.sede_id')
            ->join('detalle_compras', 'detalle_compras.lote_id', '=', 'movimiento_inventarios.lote_id')
            ->where('detalle_compras.compra_id', $compra_id)
            ->select('sede.id', 'sede.nombre')
            ->first();
    }

    public static function obtenerCompra($id)
    {
        return DB::table('compras')
            ->join('proveedors', 'proveedors.id', '=', 'compras.proveedor_id')
            ->select(
                'compras.*',
                'proveedors.nombre AS proveedor_nombre',
                'proveedors.empresa AS proveedor_empresa',
                'proveedors.id AS proveedor_id',
                'proveedors.email AS proveedor_email',
                'proveedors.telefono AS proveedor_telefono'
            )
            ->where('compras.id', $id)
            ->first();
    }

    public static function obtenerTodosDetallesCompras($buscar, $estado)
    {
        $query = DB::table('detalle_compras')
            ->join('productos', 'productos.id', '=', 'detalle_compras.producto_id')
            ->join('compras', 'compras.id', '=', 'detalle_compras.compra_id')
            ->join('proveedors', 'proveedors.id', '=', 'compras.proveedor_id')
            ->join('unidades', 'unidades.id', '=', 'detalle_compras.unidad_id')
            ->select(
                'detalle_compras.*',
                'compras.fecha',
                'proveedors.empresa as proveedor_empresa',
                'productos.nombre as producto_nombre',
                'unidades.nombre as unidad_nombre',
                'unidades.abreviatura as unidad_abreviatura'
            )
            ->orderBy('detalle_compras.id');

        if ($estado) {
            $query->where('detalle_compras.estado', $estado);
        }

        if ($buscar) {
            $query->where('detalle_compras.id', 'like', "%{$buscar}%");
        }

        return $query->get();
    }

    public static function eliminarCompra($id)
    {
        DB::beginTransaction();

        try {
            $detalles = DB::table('detalle_compras')
                ->where('compra_id', $id)
                ->get();

            foreach ($detalles as $detalle) {
                $usoLote = DB::table('inventario_sede_lotes')
                    ->where('lote_id', $detalle->lote_id)
                    ->exists();

                if ($usoLote) {
                    throw new \Exception('No se puede eliminar la compra porque uno de sus lotes está siendo utilizado en el inventario.');
                }

                DB::table('lotes')->where('id', $detalle->lote_id)->delete();
                DB::table('detalle_compras')->where('id', $detalle->id)->delete();
            }

            DB::table('compras')->where('id', $id)->delete();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar la compra: ' . $e->getMessage());
            return false;
        }
    }

    public static function finalizarCompra($compra, $sede_id)
    {
        DB::beginTransaction();
        try {
            $detalles = DB::table('detalle_compras')
                ->where('compra_id', $compra->id)
                ->get();

            foreach ($detalles as $detalle) {
                $inventario = DB::table('inventario_sede_lotes')
                    ->where('lote_id', $detalle->lote_id)
                    ->where('sede_id', $sede_id)
                    ->first();

                if ($inventario) {
                    DB::table('inventario_sede_lotes')
                        ->where('id', $inventario->id)
                        ->update([
                            'cantidad'         => $inventario->cantidad + $detalle->cantidad,
                            'cantidad_convertida'  => $inventario->cantidad_convertida + $detalle->cantidad_convertida
                        ]);
                } else {
                    DB::table('inventario_sede_lotes')->insert([
                        'lote_id'         => $detalle->lote_id,
                        'sede_id'         => $sede_id,
                        'cantidad'        => $detalle->cantidad,
                        'cantidad_convertida' => $detalle->cantidad_convertida
                    ]);
                }

                DB::table('movimiento_inventarios')->insert([
                    'producto_id'     => $detalle->producto_id,
                    'lote_id'         => $detalle->lote_id,
                    'sede_id'         => $sede_id,
                    'tipo_movimiento' => 'ENTRADA',
                    'unidad_id'       => $detalle->unidad_id,
                    'cantidad'        => $detalle->cantidad,
                    'cantidad_convertida' => $detalle->cantidad_convertida,
                    'fecha'           => now(),
                ]);
            }

            DB::table('compras')
                ->where('id', $compra->id)
                ->update([
                    'estado'     => 'Finalizada',
                    'updated_at' => now()
                ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function finalizarCompraDistribuida($compra)
    {
        DB::beginTransaction();

        try {
            $sedes = DB::table('sede')
                ->where('activo', 1)
                ->orderByRaw("id = 1 DESC")
                ->get();

            if ($sedes->isEmpty()) {
                throw new \Exception('No existen sedes activas.');
            }

            $acariguaId = 1;
            $cantidadSedes = $sedes->count();

            $detalles = DB::table('detalle_compras')
                ->where('compra_id', $compra->id)
                ->get();

            foreach ($detalles as $detalle) {
                $producto = DB::table('productos')->where('id', $detalle->producto_id)->first();
                if (!$producto) {
                    throw new \Exception("Producto {$detalle->producto_id} no encontrado.");
                }

                $stockMaximo = $producto->stock_maximo;
                $gramosPorUnidad = $detalle->cantidad_convertida / $detalle->cantidad;
                $cantidadBase = intdiv($detalle->cantidad, $cantidadSedes);
                $resto = $detalle->cantidad % $cantidadSedes;
                $cantidadRestante = 0;

                foreach ($sedes as $index => $sede) {
                    $cantidadEquitativa = $cantidadBase + ($index < $resto ? 1 : 0);

                    if ($cantidadEquitativa <= 0) {
                        continue;
                    }

                    if ($sede->id != $acariguaId) {
                        $stockActual = DB::table('inventario_sede_lotes')
                            ->join('lotes', 'lotes.id', '=', 'inventario_sede_lotes.lote_id')
                            ->where('lotes.producto_id', $producto->id)
                            ->where('inventario_sede_lotes.sede_id', $sede->id)
                            ->sum('inventario_sede_lotes.cantidad');

                        $espacioDisponible = max(0, $stockMaximo - $stockActual);
                        $cantidadAsignada = min($cantidadEquitativa, $espacioDisponible);
                        $sobrante = $cantidadEquitativa - $cantidadAsignada;
                        $cantidadRestante += $sobrante;
                    } else {
                        $cantidadAsignada = $cantidadEquitativa;
                    }

                    if ($cantidadAsignada > 0) {
                        $gramosAsignados = $cantidadAsignada * $gramosPorUnidad;
                        $inventario = DB::table('inventario_sede_lotes')
                            ->where('lote_id', $detalle->lote_id)
                            ->where('sede_id', $sede->id)
                            ->first();

                        if ($inventario) {
                            DB::table('inventario_sede_lotes')
                                ->where('id', $inventario->id)
                                ->update([
                                    'cantidad'        => $inventario->cantidad + $cantidadAsignada,
                                    'cantidad_convertida' => $inventario->cantidad_convertida + $gramosAsignados,
                                ]);
                        } else {
                            DB::table('inventario_sede_lotes')->insert([
                                'lote_id'         => $detalle->lote_id,
                                'sede_id'         => $sede->id,
                                'cantidad'        => $cantidadAsignada,
                                'cantidad_convertida' => $gramosAsignados,
                            ]);
                        }

                        DB::table('movimiento_inventarios')->insert([
                            'producto_id'     => $detalle->producto_id,
                            'lote_id'         => $detalle->lote_id,
                            'sede_id'         => $sede->id,
                            'tipo_movimiento' => 'ENTRADA',
                            'unidad_id'       => $detalle->unidad_id,
                            'cantidad'        => $cantidadAsignada,
                            'cantidad_convertida' => $gramosAsignados,
                            'fecha'           => now(),
                        ]);
                    }
                }

                if ($cantidadRestante > 0) {
                    $gramosAsignados = $cantidadRestante * $gramosPorUnidad;

                    $inventario = DB::table('inventario_sede_lotes')
                        ->where('lote_id', $detalle->lote_id)
                        ->where('sede_id', $acariguaId)
                        ->first();

                    if ($inventario) {
                        DB::table('inventario_sede_lotes')
                            ->where('id', $inventario->id)
                            ->update([
                                'cantidad'        => $inventario->cantidad + $cantidadRestante,
                                'cantidad_convertida' => $inventario->cantidad_convertida + $gramosAsignados,
                            ]);
                    } else {
                        DB::table('inventario_sede_lotes')->insert([
                            'lote_id'         => $detalle->lote_id,
                            'sede_id'         => $acariguaId,
                            'cantidad'        => $cantidadRestante,
                            'cantidad_convertida' => $gramosAsignados,
                        ]);
                    }

                    DB::table('movimiento_inventarios')->insert([
                        'producto_id'     => $detalle->producto_id,
                        'lote_id'         => $detalle->lote_id,
                        'sede_id'         => $acariguaId,
                        'tipo_movimiento' => 'ENTRADA',
                        'unidad_id'       => $detalle->unidad_id,
                        'cantidad'        => $cantidadRestante,
                        'cantidad_convertida' => $gramosAsignados,
                        'fecha'           => now(),
                    ]);
                }
            }

            DB::table('compras')
                ->where('id', $compra->id)
                ->update([
                    'estado' => 'Finalizada',
                    'updated_at' => now(),
                ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
