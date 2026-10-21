<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsumoDiarioDetalle extends Model
{
    protected $table = 'consumo_diario_detalles';

    protected $fillable = [
        'consumo_diario_id',
        'producto_id',
        'cantidad_total',
        'unidad_id',
    ];

    public function consumoDiario()
    {
        return $this->belongsTo(ConsumoDiario::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function unidad()
    {
        return $this->belongsTo(Unidad::class);
    }
}
