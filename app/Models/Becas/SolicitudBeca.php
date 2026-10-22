<?php

namespace App\Models\Becas;

use App\Models\Persona;
use App\Models\Usuario;
use App\Traits\PerteneceASedePorRelacion;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SolicitudBeca extends Model
{
    use PerteneceASedePorRelacion;
    use RegistraAutoria;
    use SoftDeletes;

    protected $relacionSede = 'persona';

    protected $table = 'solicitud_beneficio';

    protected $primaryKey = 'id_sob';

    protected $fillable = [
        'per_id',
        'bes_id',
        'jnb_id',
        'tipo_solicitud_sob',
        'estado_solicitud_sob',
        'laa_id',
        'comentario_verificador_sob',
        'verificado_por_use_id',
        'fecha_verificacion_sob',
        'status_id',
    ];

    protected $casts = [
        'estado_solicitud_sob'   => 'integer',
        'fecha_verificacion_sob' => 'datetime',
        'status_id'              => 'integer',
    ];

    public function respuestas()
    {
        return $this->hasMany(SolicitudRespuesta::class, 'sob_id', 'id_sob');
    }

    public function getEstadoTextoAttribute(): string
    {
        return match ($this->estado_solicitud_sob) {
            0 => 'Pendiente',
            1 => 'Aprobado',
            2 => 'Rechazado',
            default => 'Desconocido',
        };
    }

    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado_solicitud_sob) {
            0 => 'bg-yellow-50 text-yellow-700 border-yellow-200/50 dark:bg-yellow-950/20 dark:text-yellow-400 dark:border-yellow-900/30',
            1 => 'bg-green-50 text-green-700 border-green-200/50 dark:bg-green-950/20 dark:text-green-400 dark:border-green-900/30',
            2 => 'bg-red-50 text-red-700 border-red-200/50 dark:bg-red-950/20 dark:text-red-400 dark:border-red-900/30',
            default => 'bg-slate-50 text-slate-700 border-slate-200/50 dark:bg-slate-900/20 dark:text-slate-400 dark:border-slate-800/30',
        };
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'per_id', 'id_per');
    }

    public function beneficio()
    {
        return $this->belongsTo(Beneficio::class, 'bes_id', 'id_bes');
    }

    public function jornada()
    {
        return $this->belongsTo(JornadaBeca::class, 'jnb_id', 'id_jnb');
    }

    public function lapso()
    {
        return $this->belongsTo(Lapso::class, 'laa_id', 'id_laa');
    }

    public function verificador()
    {
        return $this->belongsTo(Usuario::class, 'verificado_por_use_id', 'id_use');
    }

    public function documento()
    {
        return $this->hasOne(SolicitudDocumento::class, 'sob_id', 'id_sob');
    }

    public function asignacion()
    {
        return $this->hasOne(BecaAsignada::class, 'sob_id', 'id_sob');
    }
}
