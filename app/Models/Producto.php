<?php

namespace App\Models;

use App\Models\salud\EnvasePrimario;
use App\Traits\ConvierteAMayusculasNoEloquent;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Producto extends Model
{
    use ConvierteAMayusculasNoEloquent;
    use HasFactory;
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'producto';

    protected $primaryKey = 'id_pdt';

    protected $fillable = [
        'codigo_pdt',
        'nombre_pdt',
        'descripcion_pdt',
        'imagen_pdt',
        'precio_compra_pdt',
        'stock_minimo_pdt',
        'stock_maximo_pdt',
        'peso_contenido_pdt',
        'unidades_presentacion_pdt',
        'presentacion_dispensacion_pdt',
        'cap_id',
        'unm_id',
        'enp_id',
        'status_id',
    ];

    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unm_id', 'id_unm');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'cap_id', 'id_cap');
    }

    public function presentacion()
    {
        return $this->belongsTo(EnvasePrimario::class, 'enp_id', 'id_enp');
    }

    public function precioProducto()
    {
        return $this->hasOne(PrecioProducto::class, 'pdt_id', 'id_pdt')->latestOfMany('id_ppr');
    }

    public function lotes()
    {
        return $this->hasMany(Lote::class, 'pdt_id', 'id_pdt');
    }

    public function totales()
    {
        return $this->hasMany(InventarioTotal::class, 'pdt_id', 'id_pdt');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'pdt_id', 'id_pdt');
    }

    public function detalleCompras()
    {
        return $this->hasMany(DetalleCompra::class, 'pdt_id', 'id_pdt');
    }

    public function recetaIngredientes()
    {
        return $this->hasMany(RecetaIngrediente::class, 'pdt_id', 'id_pdt');
    }

    public static function listarProductos($buscar = null, $activo = 1, $categoria = null, $perPage = 10, $cantidadMin = null, $cantidadMax = null, $tipoProductoId = null)
    {
        $query = self::with(['categoria', 'unidad'])
            ->select('producto.*')
            ->join('categoria_producto', 'producto.cap_id', '=', 'categoria_producto.id_cap')
            ->withSum('totales as existencia_convertida', 'cantidad_convertida_total_int')
            ->withSum('totales as total_presentacion', 'cantidad_total_int');

        if ($tipoProductoId !== null) {
            $query->where('categoria_producto.tpr_id', $tipoProductoId);
        }

        if (!empty($buscar)) {
            $query->where(function ($q) use ($buscar) {
                $q->where('producto.codigo_pdt', 'like', "%{$buscar}%")
                    ->orWhere('producto.nombre_pdt', 'like', "%{$buscar}%");
            });
        }

        if ($activo !== null && $activo !== '') {
            $query->where('producto.status_id', (int) $activo === 1 ? self::STATUS_ACTIVO : self::STATUS_INACTIVO);
        } else {
            $query->where('producto.status_id', self::STATUS_ACTIVO);
        }

        if ($cantidadMin !== null) {
            $query->having('existencia_convertida', '>=', $cantidadMin);
        }
        if ($cantidadMax !== null) {
            $query->having('existencia_convertida', '<=', $cantidadMax);
        }

        if ($categoria !== null && $categoria !== '') {
            $query->where('producto.cap_id', $categoria);
        }

        return $query->orderByDesc('existencia_convertida')->paginate($perPage)->withQueryString();
    }

    public function getCantidadEnSede($sedeId)
    {
        return DB::table('inventario_sede_lote')
            ->join('lote', 'lote.id_lot', '=', 'inventario_sede_lote.lot_id')
            ->where('lote.pdt_id', $this->id_pdt)
            ->where('inventario_sede_lote.sed_id', $sedeId)
            ->sum('inventario_sede_lote.cantidad_convertida_isl');
    }

    public static function getDatosFormulario(?int $tipoProductoId = null)
    {
        $query = DB::table('categoria_producto')
            ->select('id_cap', 'nombre_cap')
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->orderBy('nombre_cap', 'asc');

        if ($tipoProductoId !== null) {
            $query->where('tpr_id', $tipoProductoId);
        }

        return [
            'categorias' => $query->get(),
            'unidades'   => DB::table('unidad_medida')->select('id_unm', 'nombre_unm', 'abreviatura_unm')->whereNull('deleted_at')->orderBy('nombre_unm', 'asc')->get(),
            'envases'    => $tipoProductoId === null ? collect() : EnvasePrimario::deTipos([$tipoProductoId])->get(),
        ];
    }

    public static function crearProducto(array $data)
    {
        $helper = new self();

        $data = $helper->convertirCamposAMayusculas($data, ['nombre', 'descripcion']);
        return DB::transaction(function () use ($data) {

            if (empty($data['codigo'])) {
                $categoriaNombre = DB::table('categoria_producto')
                    ->where('id_cap', $data['categoria_id'])
                    ->value('nombre_cap') ?? 'CAT';

                $data['codigo'] = self::generarCodigoProducto(
                    $categoriaNombre,
                    $data['nombre']
                );
            } else {
                $data['codigo'] = strtoupper($data['codigo']);
            }

            $unidadId = $data['unidad_id'] ?? null;
            $pesoBase = isset($data['peso_contenido_pdt']) && $unidadId
                ? $data['peso_contenido_pdt']
                : 0;

            $presentacionId = $data['presentacion_id']
                ?? $data['envase_primario_id']
                ?? null;

            $productoId = DB::table('producto')->insertGetId([
                'cap_id'                        => $data['categoria_id'],
                'codigo_pdt'                    => $data['codigo'],
                'nombre_pdt'                    => $data['nombre'],
                'descripcion_pdt'               => $data['descripcion'] ?? null,
                'imagen_pdt'                    => $data['imagen'] ?? 'imagenes/productos/product-defect.webp',
                'precio_compra_pdt'             => 0,
                'stock_minimo_pdt'              => $data['stock_minimo_pdt'] ?? 0,
                'stock_maximo_pdt'              => $data['stock_maximo_pdt'] ?? 0,
                'peso_contenido_pdt'            => $pesoBase,
                'unidades_presentacion_pdt'     => $data['unidades_presentacion_pdt'] ?? 1,
                'presentacion_dispensacion_pdt' => $data['presentacion_dispensacion_pdt'] ?? null,
                'unm_id'                        => $unidadId,
                'enp_id'                        => $presentacionId,
                'status_id'                     => self::statusDesde($data['estado'] ?? 1),
                'created_id'                    => Auth::id(),
                'created_at'                    => now(),
                'updated_at'                    => now(),
            ], 'id_pdt');

            PrecioProducto::create([
                'pdt_id'        => $productoId,
                'costo_usd_ppr' => $data['costo_usd_ppr'],
                'margen_ppr'    => $data['margen'] ?? 0,
            ]);

            return $productoId;
        });
    }

    public static function obtenerProducto($id)
    {
        $producto = DB::table('producto')
            ->select(
                'producto.*',
                'categoria_producto.nombre_cap as categoria_nombre',
                'unidad_medida.nombre_unm as unidad_nombre',
                'unidad_medida.abreviatura_unm as unidad_abreviatura'
            )
            ->leftJoin('categoria_producto', 'producto.cap_id', '=', 'categoria_producto.id_cap')
            ->leftJoin('unidad_medida', 'producto.unm_id', '=', 'unidad_medida.id_unm')
            ->where('producto.id_pdt', $id)
            ->first();

        if (!$producto) return null;

        $producto->categoria = (object) [
            'nombre_cap' => $producto->categoria_nombre,
        ];

        $producto->unidad = (object) [
            'nombre_unm'      => $producto->unidad_nombre,
            'abreviatura_unm' => $producto->unidad_abreviatura,
        ];

        return $producto;
    }

    public static function actualizarProducto($id, array $data)
    {
        $helper = new self();

        $data = $helper->convertirCamposAMayusculas($data, ['nombre', 'descripcion']);
        $unidadId = $data['unidad_id'] ?? null;
        $pesoBase = isset($data['peso_contenido_pdt']) && $unidadId
            ? $data['peso_contenido_pdt']
            : 0;

        $precioUsd = $data['costo_usd_ppr'] ?? $data['precio_compra'] ?? 0;
        $productoAntiguo = DB::table('producto')->where('id_pdt', $id)->first();
        $codigoFinal = $productoAntiguo->codigo_pdt;

        if (!empty($data['codigo'])) {
            $codigoFinal = strtoupper($data['codigo']);
        } else if ($productoAntiguo->nombre_pdt !== $data['nombre'] || $productoAntiguo->cap_id != $data['categoria_id']) {

            $categoriaNombre = DB::table('categoria_producto')
                ->where('id_cap', $data['categoria_id'])
                ->value('nombre_cap') ?? 'CAT';

            $codigoFinal = self::generarCodigoProducto(
                $categoriaNombre,
                $data['nombre']
            );
        }

        $presentacionId = $data['presentacion_id']
            ?? $data['envase_primario_id']
            ?? null;

        $update = [
            'codigo_pdt'                    => $codigoFinal,
            'cap_id'                        => $data['categoria_id'],
            'nombre_pdt'                    => $data['nombre'],
            'descripcion_pdt'               => $data['descripcion'] ?? null,
            'precio_compra_pdt'             => $precioUsd,
            'stock_minimo_pdt'              => $data['stock_minimo_pdt'] ?? 0,
            'stock_maximo_pdt'              => $data['stock_maximo_pdt'] ?? 0,
            'peso_contenido_pdt'            => $pesoBase,
            'unm_id'                        => $unidadId,
            'unidades_presentacion_pdt'     => $data['unidades_presentacion_pdt'] ?? 1,
            'presentacion_dispensacion_pdt' => $data['presentacion_dispensacion_pdt'] ?? null,
            'enp_id'                        => $presentacionId,
            'status_id'                     => self::statusDesde($data['estado'] ?? 1),
            'updated_id'                    => Auth::id(),
            'updated_at'                    => now(),
        ];

        if (!empty($data['imagen'])) {
            $update['imagen_pdt'] = $data['imagen'];
        }

        $ultimoPrecio = PrecioProducto::where('pdt_id', $id)->latest('id_ppr')->first();
        $margenRequest = $data['margen'] ?? 0;

        if (!$ultimoPrecio || $ultimoPrecio->costo_usd_ppr != $precioUsd || $ultimoPrecio->margen_ppr != $margenRequest) {
            PrecioProducto::create([
                'pdt_id'        => $id,
                'costo_usd_ppr' => $precioUsd,
                'margen_ppr'    => $margenRequest,
            ]);
        }

        return DB::table('producto')->where('id_pdt', $id)->update($update);
    }

    public static function eliminarProducto($id)
    {
        return DB::table('producto')
            ->where('id_pdt', $id)
            ->update([
                'status_id'  => self::STATUS_INACTIVO,
                'updated_id' => Auth::id(),
                'updated_at' => now(),
            ]);
    }

    public static function activarProducto($id)
    {
        return DB::table('producto')
            ->where('id_pdt', $id)
            ->update([
                'status_id'  => self::STATUS_ACTIVO,
                'updated_id' => Auth::id(),
                'updated_at' => now(),
            ]);
    }

    protected static function statusDesde($estado): int
    {
        return (int) $estado === 1 ? self::STATUS_ACTIVO : self::STATUS_INACTIVO;
    }

    protected static function generarCodigoProducto($categoriaNombre, $nombreProducto)
    {
        $cat = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $categoriaNombre), 0, 3) ?: 'CAT');
        $prod = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $nombreProducto), 0, 3) ?: 'PRD');
        $base = "{$cat}-{$prod}";
        $suf = 1;
        do {
            $codigo = "{$base}-" . str_pad($suf, 3, '0', STR_PAD_LEFT);
            $exists = DB::table('producto')->where('codigo_pdt', $codigo)->exists();
            $suf++;
        } while ($exists && $suf < 999);

        if ($exists) {
            $codigo = "{$base}-" . strtoupper(substr(uniqid(), -4));
        }

        return $codigo;
    }

    public static function tieneInventarioEnAcarigua($productoId)
    {
        return InventarioTotal::where('pdt_id', $productoId)->where('cantidad_convertida_total_int', '>', 0)->exists();
    }

    public static function cantidadEnAcarigua($productoId)
    {
        return (float) InventarioTotal::where('pdt_id', $productoId)->sum('cantidad_convertida_total_int');
    }
}