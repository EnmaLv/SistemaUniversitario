<?php

namespace App\Models;

use App\Traits\ConvierteAMayusculasNoEloquent;
use App\Traits\PerteneceASede;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Compra extends Model
{
    use ConvierteAMayusculasNoEloquent;
    use PerteneceASede;
    use RegistraAutoria;
    use SoftDeletes;

    public const ESTADO_PENDIENTE = 'Pendiente';

    public const ESTADO_FINALIZADA = 'Finalizada';

    protected $table = 'compra';

    protected $primaryKey = 'id_cmp';

    protected $fillable = [
        'prv_id',
        'fecha_compra_cmp',
        'mod_id',
        'use_id',
        'sed_id',
        'total_cmp',
        'estado_compra_cmp',
        'observacion_cmp',
    ];

    protected $casts = [
        'fecha_compra_cmp' => 'datetime',
        'total_cmp'        => 'decimal:2',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'prv_id', 'id_prv');
    }

    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'mod_id', 'id_mod');
    }

    public function creador()
    {
        return $this->belongsTo(Usuario::class, 'use_id', 'id_use');
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sed_id', 'id_sed');
    }

    public function detalleCompras()
    {
        return $this->hasMany(DetalleCompra::class, 'cmp_id', 'id_cmp');
    }

    public function getDatosFormulario()
    {
        return [
            'proveedores' => DB::table('proveedor')
                ->select('id_prv', 'empresa_prv', 'nombre_contacto_prv', 'email_prv')
                ->where('status_id', 1)
                ->whereNull('deleted_at')
                ->orderBy('empresa_prv')
                ->get(),

            'modulos' => DB::table('modulo')
                ->select('id_mod', 'nombre_mod')
                ->where('status_id', 1)
                ->whereNull('deleted_at')
                ->orderBy('nombre_mod')
                ->get(),

            'productos' => DB::table('producto')
                ->select('id_pdt', 'codigo_pdt', 'nombre_pdt')
                ->where('status_id', 1)
                ->whereNull('deleted_at')
                ->orderBy('nombre_pdt')
                ->get(),

            'sedes' => DB::table('sede')
                ->select('id_sed', 'nombre_sed')
                ->where('status_id', 1)
                ->whereNull('deleted_at')
                ->orderBy('nombre_sed')
                ->get(),
        ];
    }

    public static function listarCompras($buscar = null, $estado = null, $fechas = null)
    {
        $query = \App\Support\Sedes\SedeActiva::filtrar(DB::table('compra'), 'compra.sed_id')
            ->join('proveedor', 'proveedor.id_prv', '=', 'compra.prv_id')
            ->whereNull('compra.deleted_at')
            ->select(
                'compra.*',
                'proveedor.empresa_prv as proveedor_empresa',
                'proveedor.nombre_contacto_prv as proveedor_nombre',
                'proveedor.id_prv as proveedor_id'
            );

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('proveedor.empresa_prv', 'like', "%{$buscar}%")
                    ->orWhere('proveedor.nombre_contacto_prv', 'like', "%{$buscar}%")
                    ->orWhere('compra.id_cmp', 'like', "%{$buscar}%");
            });
        }

        if ($estado !== null && $estado !== '') {
            $query->where('compra.estado_compra_cmp', $estado);
        }

        if ($fechas !== null) {
            $query->whereBetween('compra.fecha_compra_cmp', [$fechas['fecha_desde'], $fechas['fecha_hasta']]);
        }

        return $query->orderBy('compra.id_cmp', 'desc')->paginate(10)->withQueryString();
    }

    public static function crearCompra(array $data)
    {
        $helper = new self();

        $data = $helper->convertirCamposAMayusculas($data, ['observaciones']);

        return DB::table('compra')->insertGetId([
            'prv_id'            => $data['proveedor_id'],
            'mod_id'            => $data['modulo_id'] ?? null,
            'sed_id'            => $data['sed_id'] ?? Sede::idPrincipal() ?? \App\Support\Sedes\SedeActiva::idParaCrear(),
            'use_id'            => Auth::id(),
            'fecha_compra_cmp'  => $data['fecha'],
            'observacion_cmp'   => $data['observaciones'] ?? null,
            'total_cmp'         => 0,
            'estado_compra_cmp' => self::ESTADO_PENDIENTE,
            'created_id'        => Auth::id(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ], 'id_cmp');
    }

    public static function obtenerDetallesCompra($compra_id)
    {
        return DB::table('detalle_compra')
            ->join('producto', 'producto.id_pdt', '=', 'detalle_compra.pdt_id')
            ->join('lote', 'lote.id_lot', '=', 'detalle_compra.lot_id')
            ->join('unidad_medida', 'unidad_medida.id_unm', '=', 'detalle_compra.unm_id')
            ->where('detalle_compra.cmp_id', $compra_id)
            ->select(
                'detalle_compra.*',
                'producto.nombre_pdt as producto_nombre',
                'producto.codigo_pdt as producto_codigo',
                'lote.codigo_lote_lot',
                'lote.fecha_vencimiento_lot',
                'unidad_medida.nombre_unm as unidad_nombre',
                'unidad_medida.abreviatura_unm as unidad_abreviatura'
            )
            ->orderBy('detalle_compra.id_dcm')
            ->get();
    }

    public static function obtenerRepartoAplicado($compra_id)
    {
        return app(\App\Support\Inventario\RepartoCompra::class)->aplicado((int) $compra_id);
    }

    public static function obtenerCompra($id)
    {
        return \App\Support\Sedes\SedeActiva::filtrar(DB::table('compra'), 'compra.sed_id')
            ->join('proveedor', 'proveedor.id_prv', '=', 'compra.prv_id')
            ->select(
                'compra.*',
                'proveedor.nombre_contacto_prv AS proveedor_nombre',
                'proveedor.empresa_prv AS proveedor_empresa',
                'proveedor.id_prv AS proveedor_id',
                'proveedor.email_prv AS proveedor_email',
                'proveedor.telefono_prv AS proveedor_telefono'
            )
            ->where('compra.id_cmp', $id)
            ->whereNull('compra.deleted_at')
            ->first();
    }

    public static function obtenerTodosDetallesCompras($buscar, $estado)
    {
        $query = DB::table('detalle_compra')
            ->join('producto', 'producto.id_pdt', '=', 'detalle_compra.pdt_id')
            ->join('compra', 'compra.id_cmp', '=', 'detalle_compra.cmp_id')
            ->when(\App\Support\Sedes\SedeActiva::id(), fn ($q, $sede) => $q->where('compra.sed_id', $sede))
            ->join('proveedor', 'proveedor.id_prv', '=', 'compra.prv_id')
            ->join('unidad_medida', 'unidad_medida.id_unm', '=', 'detalle_compra.unm_id')
            ->select(
                'detalle_compra.*',
                'compra.fecha_compra_cmp',
                'proveedor.empresa_prv as proveedor_empresa',
                'producto.nombre_pdt as producto_nombre',
                'unidad_medida.nombre_unm as unidad_nombre',
                'unidad_medida.abreviatura_unm as unidad_abreviatura'
            )
            ->orderBy('detalle_compra.id_dcm');

        if ($estado) {
            $query->where('compra.estado_compra_cmp', $estado);
        }

        if ($buscar) {
            $query->where('detalle_compra.id_dcm', 'like', "%{$buscar}%");
        }

        return $query->get();
    }

    public static function eliminarCompra($id)
    {
        DB::beginTransaction();

        try {
            $detalles = DB::table('detalle_compra')
                ->where('cmp_id', $id)
                ->get();

            foreach ($detalles as $detalle) {
                $usoLote = DB::table('inventario_sede_lote')
                    ->where('lot_id', $detalle->lot_id)
                    ->exists();

                if ($usoLote) {
                    throw new \Exception('No se puede eliminar la compra porque uno de sus lotes está siendo utilizado en el inventario.');
                }

                DB::table('lote')->where('id_lot', $detalle->lot_id)->delete();
                DB::table('detalle_compra')->where('id_dcm', $detalle->id_dcm)->delete();
            }

            DB::table('compra')->where('id_cmp', $id)->delete();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar la compra: ' . $e->getMessage());
            return false;
        }
    }

    public static function finalizarCompraDistribuida($compra)
    {
        return app(\App\Support\Inventario\RepartoCompra::class)->aplicar($compra instanceof self ? $compra : (int) $compra);
    }
}
