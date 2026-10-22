<?php

namespace App\Models;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusTrazadoRuta extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'trazado_ruta_transporte';

    protected $primaryKey = 'id_trt';

    protected $fillable = [
        'rtr_id',
        'version_trt',
        'orden_trt',
        'lat_trt',
        'lng_trt',
        'vjt_id',
        'vigente_trt',
        'status_id',
    ];

    protected $casts = [
        'version_trt' => 'integer',
        'orden_trt'   => 'integer',
        'vigente_trt' => 'boolean',
    ];

    public function ruta()
    {
        return $this->belongsTo(BusRuta::class, 'rtr_id', 'id_rtr');
    }

    public function viaje()
    {
        return $this->belongsTo(BusViaje::class, 'vjt_id', 'id_vjt');
    }
}
