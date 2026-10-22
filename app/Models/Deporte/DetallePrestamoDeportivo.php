<?php

namespace App\Models\Deporte;

use App\Models\Lote;
use App\Models\Producto;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetallePrestamoDeportivo extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'detalle_prestamo_deportivo';

    protected $primaryKey = 'id_dpd';

    protected $fillable = [
        'cantidad_dpd',
        'cantidad_devuelta_dpd',
        'cantidad_danada_dpd',
        'cantidad_consumida_dpd',
        'cantidad_perdida_dpd',
        'prd_id',
        'pdt_id',
        'lot_id',
        'status_id',
    ];

    protected $casts = [
        'cantidad_dpd'           => 'float',
        'cantidad_devuelta_dpd'  => 'float',
        'cantidad_danada_dpd'    => 'float',
        'cantidad_consumida_dpd' => 'float',
        'cantidad_perdida_dpd'   => 'float',
    ];

    public function prestamo()
    {
        return $this->belongsTo(PrestamoDeportivo::class, 'prd_id', 'id_prd');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'pdt_id', 'id_pdt')->withTrashed();
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'lot_id', 'id_lot')->withTrashed();
    }

    public function porReponer(): float
    {
        return (float) $this->cantidad_danada_dpd + (float) $this->cantidad_perdida_dpd;
    }

    public static function numero(float $valor): string
    {
        $texto = number_format($valor, 2, ',', '.');

        return str_contains($texto, ',') ? rtrim(rtrim($texto, '0'), ',') : $texto;
    }
}
