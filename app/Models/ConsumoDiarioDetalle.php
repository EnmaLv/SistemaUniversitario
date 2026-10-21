<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsumoDiarioDetalle extends Model
{
    protected $table = 'consumo_diario_detalles';

    protected $fillable = [
        'consumo_diario_id',
        'pdt_id',
        'cantidad_total',
        'unm_id',
    ];

    public function consumoDiario()
    {
        return $this->belongsTo(ConsumoDiario::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'pdt_id', 'id_pdt');
    }

    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unm_id', 'id_unm');
    }
}
