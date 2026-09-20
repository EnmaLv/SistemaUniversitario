<?php

namespace App\Models\Becas;

use Illuminate\Database\Eloquent\Model;
use App\Models\Becas\Beneficio;

class BeneficioCriterio extends Model
{
    protected $table = 'be_beneficio_criterios';

    protected $fillable = [
        'id_be_beneficio',
        'id_pregunta',
        'es_eliminatoria',
        'operador',
        'valor_esperado',
        'peso',
    ];

    public function pregunta()
    {
        return $this->belongsTo(BecaPregunta::class, 'id_pregunta');
    }

    public function beneficio()
    {
        return $this->belongsTo(Beneficio::class, 'id_be_beneficio');
    }

}
