<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViajePasajero extends Model
{
    protected $table = 'viaje_pasajeros';
    public $timestamps = false;

    protected $fillable = [
        'bus_viaje_id',
        'persona_id',
        'bus_parada_id',
        'escaneado_at',
    ];

    protected $casts = [
        'escaneado_at' => 'datetime',
    ];

    public function busViaje()
    {
        return $this->belongsTo(BusViaje::class, 'bus_viaje_id');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function busParada()
    {
        return $this->belongsTo(BusParada::class, 'bus_parada_id');
    }
}