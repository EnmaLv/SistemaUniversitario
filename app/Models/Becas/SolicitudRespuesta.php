<?php

namespace App\Models\Becas;

use Illuminate\Database\Eloquent\Model;

class SolicitudRespuesta extends Model
{
    protected $table = 'be_solicitud_respuestas';

    protected $fillable = [
        'id_solicitud',
        'id_pregunta',
        'valor',
        'valor_json',
        'cumple_criterio',
    ];

    protected $casts = [
        'valor_json'      => 'array',
        'cumple_criterio' => 'boolean',
    ];

    public function pregunta()
    {
        return $this->belongsTo(BecaPregunta::class, 'id_pregunta');
    }
}
