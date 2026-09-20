<?php

namespace App\Models\Becas;

use Illuminate\Database\Eloquent\Model;

class JornadaBeca extends Model
{
    protected $table = 'be_jornadas_becas';

    protected $fillable = [
        'nombre_jornada',
        'descripcion_jornada',
        'beneficio_id',
        'lapsos_id',
        'fecha_inicio_solicitud',
        'fecha_fin_solicitud',
        'cupos_maximos',
        'cupos_asignados',
        'activa',
    ];

    protected $casts = [
        'fecha_inicio_solicitud' => 'date',
        'fecha_fin_solicitud'    => 'date',
        'activa'                 => 'boolean',
    ];

    public function criterios()
    {
        return $this->hasMany(JornadaCriterio::class, 'id_jornada');
    }

    public function beneficio()
    {
        return $this->belongsTo(Beneficio::class, 'beneficio_id');
    }

    public function lapso()
    {
        return $this->belongsTo(Lapso::class, 'lapsos_id');
    }
}