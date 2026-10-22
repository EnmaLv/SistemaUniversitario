<?php

namespace App\Models;

use App\Traits\ConvierteAMayusculasNoEloquent;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

class Categoria extends Model
{
    use ConvierteAMayusculasNoEloquent;
    use HasFactory;
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'categoria_producto';

    protected $primaryKey = 'id_cap';

    protected $fillable = [
        'nombre_cap',
        'descripcion_cap',
        'tpr_id',
        'status_id',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('ordenAlfabetico', function (Builder $builder) {
            $builder->orderBy('categoria_producto.nombre_cap', 'asc');
        });
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'cap_id', 'id_cap');
    }

    public static function listarCategorias($buscar = null, $activo = 1, $tipoProductoId = null)
    {
        $query = self::query();

        if ($tipoProductoId !== null) {
            $query->where('tpr_id', $tipoProductoId);
        }

        if ($buscar) {
            $query->where('nombre_cap', 'like', "%{$buscar}%");
        }

        if ($activo !== null && $activo !== '') {
            $query->where('status_id', (int) $activo === 1 ? self::STATUS_ACTIVO : self::STATUS_INACTIVO);
        }

        return $query->orderBy('nombre_cap', 'asc')
            ->paginate(10)
            ->withQueryString();
    }

    public static function crearCategoria(array $data, int $tipoProductoId)
    {
        $helper = new self();

        $data = $helper->convertirCamposAMayusculas($data, ['nombre', 'descripcion']);

        return DB::table('categoria_producto')->insertGetId([
            'nombre_cap'      => $data['nombre'],
            'descripcion_cap' => $data['descripcion'] ?? null,
            'tpr_id'          => $tipoProductoId,
            'status_id'       => self::STATUS_ACTIVO,
            'created_id'      => Auth::id(),
            'created_at'      => now(),
            'updated_at'      => now(),
        ], 'id_cap');
    }

    public static function obtenerCategoria($id)
    {
        return DB::table('categoria_producto')
            ->where('id_cap', $id)
            ->first();
    }

    public static function actualizarCategoria($id, array $data)
    {
        $helper = new self();

        $data = $helper->convertirCamposAMayusculas($data, ['nombre', 'descripcion']);

        return DB::table('categoria_producto')
            ->where('id_cap', $id)
            ->update([
                'nombre_cap'      => $data['nombre'],
                'descripcion_cap' => $data['descripcion'] ?? null,
                'updated_id'      => Auth::id(),
                'updated_at'      => now(),
            ]);
    }


    public static function eliminarCategoria($id)
    {
        return DB::table('categoria_producto')
            ->where('id_cap', $id)
            ->update([
                'status_id'  => self::STATUS_INACTIVO,
                'updated_id' => Auth::id(),
                'updated_at' => now(),
            ]);
    }

    public static function activarCategoria($id)
    {
        return DB::table('categoria_producto')
            ->where('id_cap', $id)
            ->update([
                'status_id'  => self::STATUS_ACTIVO,
                'updated_id' => Auth::id(),
                'updated_at' => now(),
            ]);
    }

    public static function obtenerCategoriaConProductos($id)
    {
        $categoria = DB::table('categoria_producto')
            ->where('id_cap', $id)
            ->first();

        if ($categoria) {
            $categoria->productos = DB::table('producto')
                ->where('cap_id', $id)
                ->where('status_id', self::STATUS_ACTIVO)
                ->whereNull('deleted_at')
                ->select('id_pdt', 'codigo_pdt', 'nombre_pdt', 'precio_compra_pdt', 'status_id')
                ->get();

            $categoria->cantidad_productos = $categoria->productos->count();
        }

        return $categoria;
    }

    public static function tieneProductos($id)
    {
        return DB::table('producto')
            ->where('cap_id', $id)
            ->exists();
    }

    public static function contarProductos($id)
    {
        return DB::table('producto')
            ->where('cap_id', $id)
            ->count();
    }

    public static function obtenerCategoriasActivas()
    {
        return DB::table('categoria_producto')
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->select('id_cap', 'nombre_cap')
            ->orderBy('nombre_cap', 'asc')
            ->get();
    }

    public function tipoProducto()
    {
        return $this->belongsTo(TipoProducto::class, 'tpr_id', 'id_tpr');
    }

    public static function reglaDeTipos(array $tipoIds): Exists
    {
        return Rule::exists('categoria_producto', 'id_cap')
            ->whereIn('tpr_id', array_map('intval', $tipoIds))
            ->whereNull('deleted_at');
    }
}
