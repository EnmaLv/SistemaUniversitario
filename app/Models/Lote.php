<?php

namespace App\Models;

use App\Models\salud\Dispensacion;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lote extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'lote';

    protected $primaryKey = 'id_lot';

    protected $fillable = [
        'codigo_lote_lot',
        'fecha_entrada_lot',
        'fecha_vencimiento_lot',
        'cantidad_inicial_lot',
        'cantidad_actual_lot',
        'precio_compra_lot',
        'status_id',
        'pdt_id',
        'prv_id',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'pdt_id', 'id_pdt');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'prv_id', 'id_prv');
    }

    public function inventarioSedeLotes()
    {
        return $this->hasMany(InventarioSedeLote::class, 'lot_id', 'id_lot');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'lot_id', 'id_lot');
    }

    public function detalleCompras()
    {
        return $this->hasMany(DetalleCompra::class, 'lot_id', 'id_lot');
    }

    public function dispensaciones()
    {
        return $this->hasMany(Dispensacion::class, 'lot_id', 'id_lot');
    }

    public static function disponiblesParaProducto($productoId, ?int $sedeId = null)
    {
        return self::where('pdt_id', $productoId)
            ->where('cantidad_actual_lot', '>', 0)
            ->where(function ($query) {
                $query->whereNull('fecha_vencimiento_lot')
                    ->orWhere('fecha_vencimiento_lot', '>=', now()->toDateString());
            })
            ->with('inventarioSedeLotes')
            ->orderBy('fecha_vencimiento_lot', 'asc')
            ->get();
    }
}
