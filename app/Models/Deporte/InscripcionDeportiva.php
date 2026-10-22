<?php

namespace App\Models\Deporte;

use App\Models\Persona;
use App\Models\Usuario;
use App\Traits\PerteneceASedePorRelacion;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InscripcionDeportiva extends Model
{
    use PerteneceASedePorRelacion;
    use RegistraAutoria;
    use SoftDeletes;

    public const ESTADO_INSCRITO = 'inscrito';

    public const ESTADO_ASISTIO = 'asistio';

    public const ESTADO_NO_ASISTIO = 'no_asistio';

    public const ESTADO_RETIRADO = 'retirado';

    protected $relacionSede = 'horario';

    protected $table = 'inscripcion_deportiva';

    protected $primaryKey = 'id_ide';

    protected $fillable = [
        'fecha_registro_ide',
        'estado_ide',
        'observaciones_ide',
        'hde_id',
        'per_id',
        'use_id',
        'status_id',
    ];

    protected $casts = [
        'fecha_registro_ide' => 'date',
        'status_id'          => 'integer',
    ];

    public function horario()
    {
        return $this->belongsTo(DeporteHorario::class, 'hde_id', 'id_hde');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'per_id', 'id_per');
    }

    public function registradoPor()
    {
        return $this->belongsTo(Usuario::class, 'use_id', 'id_use');
    }

    public function asistencias()
    {
        return $this->hasMany(AsistenciaDeportiva::class, 'ide_id', 'id_ide');
    }

    public function estaVigente(): bool
    {
        return $this->estado_ide !== self::ESTADO_RETIRADO;
    }
}
