<?php

namespace App\Models\Becas;

use Illuminate\Database\Eloquent\Model;

class BecaAsignada extends Model
{
    protected $table = 'beca_asignada';

    protected $fillable = [
        'id_solicitud',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'motivo_revocacion',
    ];
}
