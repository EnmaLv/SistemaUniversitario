<?php

namespace App\Models\Becas;

use Illuminate\Database\Eloquent\Model;

class BecaPreguntaOpciones extends Model
{
    protected $table = 'be_beca_pregunta_opciones';

    protected $fillable = [
        'id_pregunta',
        'etiqueta',
        'valor',
        'orden',
        'activo',
    ];
}
