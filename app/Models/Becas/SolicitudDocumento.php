<?php

namespace App\Models\Becas;

use Illuminate\Database\Eloquent\Model;

class SolicitudDocumento extends Model
{
    protected $table = 'solicitud_documento';

    protected $fillable = [
        'id_solicitud',
        'nombre_documento',
        'ruta_archivo',
        'tipo_archivo',
    ];
}
