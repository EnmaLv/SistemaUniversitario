<?php

namespace App\Models\Deporte;

use App\Traits\PerteneceASedePorRelacion;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsistenciaDeportiva extends Model
{
    use PerteneceASedePorRelacion;
    use RegistraAutoria;
    use SoftDeletes;

    public const ESTADO_PRESENTE = 'presente';

    public const ESTADO_INASISTENTE = 'inasistente';

    protected $relacionSede = 'sesion';

    protected $table = 'asistencia_deportiva';

    protected $primaryKey = 'id_asd';

    protected $fillable = [
        'sad_id',
        'ide_id',
        'estado_asd',
        'status_id',
    ];

    protected $casts = [
        'status_id' => 'integer',
    ];

    public function sesion()
    {
        return $this->belongsTo(SesionAsistencia::class, 'sad_id', 'id_sad');
    }

    public function inscripcion()
    {
        return $this->belongsTo(InscripcionDeportiva::class, 'ide_id', 'id_ide');
    }
}
