<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BusViaje;

class BusGpsLog extends Model
{
    protected $table = 'bus_gps_logs';
    
    protected $fillable = [
        'local_id',
        'bus_viaje_id',
        'lat',
        'lng',
        'velocidad',
        'heading',
        'registrado_en',
        'origen',
    ];
    
    protected $casts = [
        'registrado_en' => 'datetime',
    ];

    public function viaje()
    {
        return $this->belongsTo(BusViaje::class, 'bus_viaje_id');
    }
}
