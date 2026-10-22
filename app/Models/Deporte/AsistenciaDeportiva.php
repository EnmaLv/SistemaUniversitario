<?php

namespace App\Models\Deporte;

use App\Models\Persona;
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
        'per_id',
        'ide_id',
        'ied_id',
        'estado_asd',
        'status_id',
    ];

    protected $casts = [
        'per_id'    => 'integer',
        'ide_id'    => 'integer',
        'ied_id'    => 'integer',
        'status_id' => 'integer',
    ];

    public function sesion()
    {
        return $this->belongsTo(SesionAsistencia::class, 'sad_id', 'id_sad');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'per_id', 'id_per');
    }

    public function inscripcion()
    {
        return $this->belongsTo(InscripcionDeportiva::class, 'ide_id', 'id_ide');
    }

    public function integrante()
    {
        return $this->belongsTo(IntegranteEquipo::class, 'ied_id', 'id_ied');
    }

    public function estabaInscrito(): bool
    {
        return $this->ide_id !== null || $this->ied_id !== null;
    }
}
