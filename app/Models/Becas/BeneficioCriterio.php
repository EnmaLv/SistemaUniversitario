<?php

namespace App\Models\Becas;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BeneficioCriterio extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'criterio_beneficio';

    protected $primaryKey = 'id_crb';

    protected $fillable = [
        'bes_id',
        'pgb_id',
        'es_eliminatoria_crb',
        'operador_crb',
        'valor_esperado_crb',
        'peso_crb',
        'status_id',
    ];

    protected $casts = [
        'es_eliminatoria_crb' => 'boolean',
        'status_id'           => 'integer',
    ];

    public function pregunta()
    {
        return $this->belongsTo(BecaPregunta::class, 'pgb_id', 'id_pgb');
    }

    public function beneficio()
    {
        return $this->belongsTo(Beneficio::class, 'bes_id', 'id_bes');
    }
}
