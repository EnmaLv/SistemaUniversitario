<?php

namespace App\Models\Becas;

use Illuminate\Database\Eloquent\Model;

class JornadaCriterio extends Model
{
    protected $table = 'be_jornada_criterios';

    protected $fillable = [
        'id_jornada',
        'id_pregunta',
        'es_eliminatoria',
        'operador',
        'valor_esperado',
        'peso',
    ];

    public function jornada()
    {
        return $this->belongsTo(JornadaBeca::class, 'id_jornada');
    }

    public function pregunta()
    {
        return $this->belongsTo(BecaPregunta::class, 'id_pregunta');
    }
}
