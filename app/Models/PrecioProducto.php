<?php

namespace App\Models;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrecioProducto extends Model
{
    use HasFactory;
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'precio_producto';

    protected $primaryKey = 'id_ppr';

    protected $fillable = [
        'pdt_id',
        'costo_usd_ppr',
        'margen_ppr',
        'precio_usd_ppr',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'pdt_id', 'id_pdt');
    }
}
