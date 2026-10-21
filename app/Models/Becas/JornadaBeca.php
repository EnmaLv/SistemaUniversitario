<?php

namespace App\Models\Becas;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JornadaBeca extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'jornada_beneficio';

    protected $primaryKey = 'id_jnb';

    protected $fillable = [
        'nombre_jornada_jnb',
        'descripcion_jornada_jnb',
        'bes_id',
        'laa_id',
        'fecha_inicio_solicitud_jnb',
        'fecha_fin_solicitud_jnb',
        'cupos_maximos_jnb',
        'cupos_asignados_jnb',
        'status_id',
    ];

    protected $casts = [
        'fecha_inicio_solicitud_jnb' => 'date',
        'fecha_fin_solicitud_jnb'    => 'date',
        'cupos_maximos_jnb'          => 'integer',
        'cupos_asignados_jnb'        => 'integer',
        'status_id'                  => 'integer',
    ];

    public function criterios()
    {
        return $this->hasMany(JornadaCriterio::class, 'jnb_id', 'id_jnb');
    }

    public function beneficio()
    {
        return $this->belongsTo(Beneficio::class, 'bes_id', 'id_bes');
    }

    public function lapso()
    {
        return $this->belongsTo(Lapso::class, 'laa_id', 'id_laa');
    }
}
