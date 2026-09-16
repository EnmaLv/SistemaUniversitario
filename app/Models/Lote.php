<?php

namespace App\Models;

use App\Models\salud\Dispensacion;
use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    protected $table = 'lotes';

    protected $fillable = [
        'codigo_lote',
        'fecha_entrada',
        'fecha_vencimiento',
        'cantidad_inicial',
        'cantidad_actual',
        'precio_compra',
        'estado',
        'producto_id',
        'proveedor_id',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function inventarioSedeLotes()
    {
        return $this->hasMany(InventarioSedeLote::class);
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    public function detalleCompras()
    {
        return $this->hasMany(DetalleCompra::class);
    }

    public function dispensaciones()
    {
        return $this->hasMany(Dispensacion::class);
    }

    /**
     * Obtiene los lotes disponibles (con stock y no vencidos) para un producto.
     */
    public static function disponiblesParaProducto($productoId)
    {
        return self::where('producto_id', $productoId)
            ->where('cantidad_actual', '>', 0)
            ->where(function ($query) {
                $query->whereNull('fecha_vencimiento')
                    ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
            })
            ->orderBy('fecha_vencimiento', 'asc')
            ->get();
    }
}
