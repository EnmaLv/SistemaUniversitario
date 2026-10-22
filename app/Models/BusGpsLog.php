<?php

namespace App\Models;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusGpsLog extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'log_gps_transporte';

    protected $primaryKey = 'id_lgt';

    protected $fillable = [
        'local_id_lgt',
        'vjt_id',
        'lat_lgt',
        'lng_lgt',
        'velocidad_lgt',
        'heading_lgt',
        'registrado_en_lgt',
        'origen_lgt',
        'es_desvio_lgt',
        'tramo_desvio_lgt',
        'status_id',
    ];

    protected $casts = [
        'registrado_en_lgt' => 'datetime',
        'es_desvio_lgt'     => 'boolean',
        'tramo_desvio_lgt'  => 'integer',
    ];

    public function viaje()
    {
        return $this->belongsTo(BusViaje::class, 'vjt_id', 'id_vjt');
    }
}
