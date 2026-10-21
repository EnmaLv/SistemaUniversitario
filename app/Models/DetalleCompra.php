<?php

namespace App\Models;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleCompra extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'detalle_compra';

    protected $primaryKey = 'id_dcm';

    protected $fillable = [
        'cmp_id',
        'pdt_id',
        'lot_id',
        'cantidad_dcm',
        'cantidad_convertida_dcm',
        'unm_id',
        'precio_unitario_dcm',
        'subtotal_dcm',
    ];

    public function compra()
    {
        return $this->belongsTo(Compra::class, 'cmp_id', 'id_cmp');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'pdt_id', 'id_pdt');
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'lot_id', 'id_lot');
    }

    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unm_id', 'id_unm');
    }
}
