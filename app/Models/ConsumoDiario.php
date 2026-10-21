<?php

namespace App\Models;

use App\Traits\PerteneceASede;
use Illuminate\Database\Eloquent\Model;

class ConsumoDiario extends Model
{
    use PerteneceASede;

    protected $table = 'consumo_diarios';

    protected $fillable = [
        'receta_id',
        'sede_id',
        'fecha_consumo_diario',
        'porciones_servidas',
    ];

    protected $casts = [
        'fecha_consumo_diario' => 'date',
    ];

    public function receta()
    {
        return $this->belongsTo(Receta::class);
    }

    public function detalles()
    {
        return $this->hasMany(ConsumoDiarioDetalle::class);
    }
}
