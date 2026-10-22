<?php

namespace App\Models\Deporte;

use App\Models\Producto;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleEntregaMaterial extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'detalle_entrega_material';

    protected $primaryKey = 'id_dem';

    protected $fillable = [
        'cantidad_dem',
        'ema_id',
        'pdt_id',
        'status_id',
    ];

    protected $casts = [
        'cantidad_dem' => 'decimal:2',
    ];

    public function entrega()
    {
        return $this->belongsTo(EntregaMaterial::class, 'ema_id', 'id_ema');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'pdt_id', 'id_pdt')->withTrashed();
    }
}
