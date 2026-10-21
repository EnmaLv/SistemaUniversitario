<?php

namespace App\Models\Becas;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JornadaCriterio extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'criterio_jornada_beneficio';

    protected $primaryKey = 'id_cjb';

    protected $fillable = [
        'jnb_id',
        'pgb_id',
        'es_eliminatoria_cjb',
        'operador_cjb',
        'valor_esperado_cjb',
        'peso_cjb',
        'status_id',
    ];

    protected $casts = [
        'es_eliminatoria_cjb' => 'boolean',
        'status_id'           => 'integer',
    ];

    public function jornada()
    {
        return $this->belongsTo(JornadaBeca::class, 'jnb_id', 'id_jnb');
    }

    public function pregunta()
    {
        return $this->belongsTo(BecaPregunta::class, 'pgb_id', 'id_pgb');
    }
}
