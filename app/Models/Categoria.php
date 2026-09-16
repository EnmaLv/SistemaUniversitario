<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB; 
use App\Traits\ConvierteAMayusculasNoEloquent;
use Illuminate\Database\Eloquent\Builder;

class Categoria extends Model
{
    use ConvierteAMayusculasNoEloquent;
    use HasFactory;

    protected $table = 'categorias';

    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo_producto_id',
        'activo',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('ordenAlfabetico', function (Builder $builder) {
            $builder->orderBy('nombre', 'asc');
        });
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'categoria_id');
    }

    public static function listarCategorias($buscar = null, $activo = 1, $tipoProductoId = null)
    {
        $query = self::query();

        if ($tipoProductoId !== null) {
            $query->where('tipo_producto_id', $tipoProductoId);
        }

        if ($buscar) {
            $query->where('nombre', 'like', "%{$buscar}%");
        }

        if ($activo !== null && $activo !== '') {
            $query->where('activo', (int)$activo);
        }

        return $query->orderBy('nombre', 'asc')
            ->paginate(10)
            ->withQueryString();
    }

    public static function crearCategoria(array $data, int $tipoProductoId)
    {
        $helper = new self();

        $data = $helper->convertirCamposAMayusculas($data, ['nombre', 'descripcion']);

        return DB::table('categorias')->insertGetId([
            'nombre'      => $data['nombre'],
            'descripcion' => $data['descripcion'],
            'tipo_producto_id' => $tipoProductoId,
            'activo'      => true,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public static function obtenerCategoria($id)
    {
        return DB::table('categorias')
            ->where('id', $id)
            ->first();
    }

    public static function actualizarCategoria($id, array $data)
    {
        $helper = new self();

        $data = $helper->convertirCamposAMayusculas($data, ['nombre', 'descripcion']);

        return DB::table('categorias')
            ->where('id', $id)
            ->update([
                'nombre'      => $data['nombre'],
                'descripcion' => $data['descripcion'],
                'updated_at'  => now(),
            ]);
    }


    public static function eliminarCategoria($id)
    {
        return DB::table('categorias')
            ->where('id', $id)
            ->update([
                'activo' => 0,
                'updated_at' => now()
            ]);
    }

    public static function activarCategoria($id)
    {
        return DB::table('categorias')
            ->where('id', $id)
            ->update([
                'activo' => 1,
                'updated_at' => now()
            ]);
    }

    public static function obtenerCategoriaConProductos($id)
    {
        $categoria = DB::table('categorias')
            ->where('id', $id)
            ->first();

        if ($categoria) {
            $categoria->productos = DB::table('productos')
                ->where('categoria_id', $id)
                ->where('activo', 1)
                ->select('id', 'codigo', 'nombre', 'precio_compra', 'estado')
                ->get();

            $categoria->cantidad_productos = $categoria->productos->count();
        }

        return $categoria;
    }

    public static function tieneProductos($id)
    {
        return DB::table('productos')
            ->where('categoria_id', $id)
            ->exists();
    }

    public static function contarProductos($id)
    {
        return DB::table('productos')
            ->where('categoria_id', $id)
            ->count();
    }

    public static function obtenerCategoriasActivas()
    {
        return DB::table('categorias')
            ->where('activo', 1)
            ->select('id', 'nombre')
            ->orderBy('nombre', 'asc')
            ->get();
    }

    public function tipoProducto()
    {
        return $this->belongsTo(TipoProducto::class, 'tipo_producto_id');
    }
}
