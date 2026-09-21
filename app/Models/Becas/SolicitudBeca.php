<?php

namespace App\Models\Becas;

use Illuminate\Database\Eloquent\Model;
use App\Models\Persona;
use App\Models\Becas\Beneficio;
use App\Models\Becas\JornadaBeca;
use App\Models\Becas\Lapso;
use App\Models\Usuario;

class SolicitudBeca extends Model
{
    protected $table = 'be_solicitud_becas';

    protected $fillable = [
        'id_persona',
        'id_beneficio',
        'jornada_id',
        'tipo_solicitud',
        'estado',
        'id_lapso',
        'comentario_verificador',
        'verificado_por',
        'fecha_verificacion',
    ];

    protected $casts = [
        'estado'              => 'integer',
        'fecha_verificacion'  => 'datetime',
    ];

    public function respuestas()
    {
        return $this->hasMany(SolicitudRespuesta::class, 'id_solicitud');
    }

    public function getEstadoTextoAttribute(): string
    {
        return match ($this->estado) {
            0 => 'Pendiente',
            1 => 'Aprobado',
            2 => 'Rechazado',
            default => 'Desconocido',
        };
    }

    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado) {
            0 => 'bg-yellow-50 text-yellow-700 border-yellow-200/50 dark:bg-yellow-950/20 dark:text-yellow-400 dark:border-yellow-900/30',
            1 => 'bg-green-50 text-green-700 border-green-200/50 dark:bg-green-950/20 dark:text-green-400 dark:border-green-900/30',
            2 => 'bg-red-50 text-red-700 border-red-200/50 dark:bg-red-950/20 dark:text-red-400 dark:border-red-900/30',
            default => 'bg-slate-50 text-slate-700 border-slate-200/50 dark:bg-slate-900/20 dark:text-slate-400 dark:border-slate-800/30',
        };
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    public function beneficio()
    {
        return $this->belongsTo(Beneficio::class, 'id_beneficio', 'id');
    }

    public function jornada()
    {
        return $this->belongsTo(JornadaBeca::class, 'jornada_id', 'id');
    }

    public function lapso()
    {
        return $this->belongsTo(Lapso::class, 'id_lapso', 'id');
    }

    public function verificador()
    {
        return $this->belongsTo(Usuario::class, 'verificado_por', 'id_usuario');
    }

    public function documento()
    {
        return $this->hasOne(SolicitudDocumento::class, 'id_solicitud');
    }
}
