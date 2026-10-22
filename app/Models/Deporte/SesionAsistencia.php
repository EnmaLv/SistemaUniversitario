<?php

namespace App\Models\Deporte;

use App\Models\Usuario;
use App\Traits\PerteneceASedePorRelacion;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SesionAsistencia extends Model
{
    use PerteneceASedePorRelacion;
    use RegistraAutoria;
    use SoftDeletes;

    protected $relacionSede = 'horario';

    protected $table = 'sesion_asistencia_deportiva';

    protected $primaryKey = 'id_sad';

    protected $fillable = [
        'hde_id',
        'fecha_sad',
        'cerrada_sad',
        'cerrada_en_sad',
        'cerrada_por_use_id',
        'status_id',
    ];

    protected $casts = [
        'fecha_sad'      => 'date',
        'cerrada_sad'    => 'boolean',
        'cerrada_en_sad' => 'datetime',
        'status_id'      => 'integer',
    ];

    public function horario()
    {
        return $this->belongsTo(DeporteHorario::class, 'hde_id', 'id_hde');
    }

    public function cerradaPor()
    {
        return $this->belongsTo(Usuario::class, 'cerrada_por_use_id', 'id_use');
    }

    public function asistencias()
    {
        return $this->hasMany(AsistenciaDeportiva::class, 'sad_id', 'id_sad');
    }

    public function presentes()
    {
        return $this->asistencias()->where('estado_asd', AsistenciaDeportiva::ESTADO_PRESENTE);
    }

    public function estaCerrada(): bool
    {
        return (bool) $this->cerrada_sad;
    }
}
