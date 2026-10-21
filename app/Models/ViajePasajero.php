<?php

namespace App\Models;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ViajePasajero extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'pasajero_viaje_transporte';

    protected $primaryKey = 'id_pvt';

    protected $fillable = [
        'vjt_id',
        'per_id',
        'ptr_id',
        'escaneado_at_pvt',
        'status_id',
    ];

    protected $casts = [
        'escaneado_at_pvt' => 'datetime',
    ];

    public function busViaje()
    {
        return $this->belongsTo(BusViaje::class, 'vjt_id', 'id_vjt');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'per_id', 'id_per');
    }

    public function busParada()
    {
        return $this->belongsTo(BusParada::class, 'ptr_id', 'id_ptr');
    }
}
