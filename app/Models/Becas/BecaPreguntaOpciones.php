<?php

namespace App\Models\Becas;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BecaPreguntaOpciones extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'opcion_pregunta_beneficio';

    protected $primaryKey = 'id_opb';

    protected $fillable = [
        'pgb_id',
        'etiqueta_opb',
        'valor_opb',
        'orden_opb',
        'status_id',
    ];

    protected $casts = [
        'status_id' => 'integer',
    ];
}
