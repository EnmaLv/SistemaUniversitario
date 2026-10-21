<?php

namespace App\Models;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusRutaParada extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'ruta_parada_transporte';

    protected $primaryKey = 'id_rpt';

    protected $fillable = [
        'rtr_id',
        'ptr_id',
        'orden_rpt',
        'status_id',
    ];

    public function busRuta()
    {
        return $this->belongsTo(BusRuta::class, 'rtr_id', 'id_rtr');
    }

    public function busParada()
    {
        return $this->belongsTo(BusParada::class, 'ptr_id', 'id_ptr');
    }
}
