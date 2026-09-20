<?php

namespace App\Models\Becas;

use App\Models\Becas\Beneficio;
use Illuminate\Database\Eloquent\Model;

class BecaPregunta extends Model
{
    protected $table = 'be_beca_preguntas';

    protected $fillable = [
        'id_be_beneficio',
        'codigo',
        'etiqueta',
        'placeholder',
        'tipo',
        'obligatoria',
        'valor_min',
        'valor_max',
        'min_length',
        'max_length',
        'regex',
        'orden',
        'activo',
    ];

    public function beca() {
        return $this->belongsTo(Beneficio::class, 'id_be_beneficio');
    }

    public function opciones() {
        return $this->hasMany(BecaPreguntaOpciones::class, 'id_pregunta')->orderBy('orden');
    }
    
    public function criterios()
    {
        return $this->hasMany(JornadaCriterio::class, 'id_pregunta');
    }
}
