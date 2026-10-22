<?php

namespace App\Models\Becas;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BecaPregunta extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'pregunta_beneficio';

    protected $primaryKey = 'id_pgb';

    protected $fillable = [
        'bes_id',
        'codigo_pgb',
        'etiqueta_pgb',
        'placeholder_pgb',
        'ayuda_pgb',
        'tipo_campo_pgb',
        'obligatoria_pgb',
        'valor_min_pgb',
        'valor_max_pgb',
        'min_length_pgb',
        'max_length_pgb',
        'regex_pgb',
        'orden_pgb',
        'status_id',
    ];

    protected $casts = [
        'obligatoria_pgb' => 'boolean',
        'status_id'       => 'integer',
    ];

    public function beca()
    {
        return $this->belongsTo(Beneficio::class, 'bes_id', 'id_bes');
    }

    public function opciones()
    {
        return $this->hasMany(BecaPreguntaOpciones::class, 'pgb_id', 'id_pgb')->orderBy('orden_opb');
    }

    public function criterios()
    {
        return $this->hasMany(JornadaCriterio::class, 'pgb_id', 'id_pgb');
    }
}
