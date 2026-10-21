<?php

namespace App\Models;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RutaHorario extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'horario_ruta_transporte';

    protected $primaryKey = 'id_hrt';

    protected $fillable = [
        'rtr_id',
        'hora_salida_hrt',
        'tipo_viaje_hrt',
        'status_id',
    ];

    public function ruta()
    {
        return $this->belongsTo(BusRuta::class, 'rtr_id', 'id_rtr');
    }
}
