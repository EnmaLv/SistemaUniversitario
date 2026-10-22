<?php

namespace App\Models\Becas;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SolicitudRespuesta extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'respuesta_solicitud_beneficio';

    protected $primaryKey = 'id_rsb';

    protected $fillable = [
        'sob_id',
        'pgb_id',
        'valor_rsb',
        'valor_json_rsb',
        'cumple_criterio_rsb',
        'status_id',
    ];

    protected $casts = [
        'valor_json_rsb'      => 'array',
        'cumple_criterio_rsb' => 'boolean',
        'status_id'           => 'integer',
    ];

    public function pregunta()
    {
        return $this->belongsTo(BecaPregunta::class, 'pgb_id', 'id_pgb');
    }
}
