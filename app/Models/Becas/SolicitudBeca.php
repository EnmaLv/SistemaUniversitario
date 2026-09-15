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
        'indice_academico',
        'direccion_temporal',
        'gasto_pasaje',
        'vivienda_transporte',
        'datos_socioeconomicos',
        'comentario_verificador',
        'verificado_por',
        'fecha_verificacion',
    ];

    protected $casts = [
        'estado' => 'integer',
        'indice_academico' => 'decimal:2',
        'gasto_pasaje' => 'decimal:2',
        'vivienda_transporte' => 'array',
        'datos_socioeconomicos' => 'array',
        'fecha_verificacion' => 'datetime',
    ];

    /**
     * Obtiene el texto representativo del estado.
     */
    public function getEstadoTextoAttribute(): string
    {
        return match ($this->estado) {
            0 => 'Pendiente',
            1 => 'Aprobado',
            2 => 'Rechazado',
            default => 'Desconocido',
        };
    }

    /**
     * Obtiene la clase de Tailwind para el badge del estado.
     */
    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado) {
            0 => 'bg-yellow-50 text-yellow-700 border-yellow-200/50 dark:bg-yellow-950/20 dark:text-yellow-400 dark:border-yellow-900/30',
            1 => 'bg-green-50 text-green-700 border-green-200/50 dark:bg-green-950/20 dark:text-green-400 dark:border-green-900/30',
            2 => 'bg-red-50 text-red-700 border-red-200/50 dark:bg-red-950/20 dark:text-red-400 dark:border-red-900/30',
            default => 'bg-slate-50 text-slate-700 border-slate-200/50 dark:bg-slate-900/20 dark:text-slate-400 dark:border-slate-800/30',
        };
    }

    /**
     * Relación con la Persona (estudiante).
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    /**
     * Relación con el Beneficio.
     */
    public function beneficio()
    {
        return $this->belongsTo(Beneficio::class, 'id_beneficio', 'id');
    }

    /**
     * Relación con la Jornada.
     */
    public function jornada()
    {
        return $this->belongsTo(JornadaBeca::class, 'jornada_id', 'id');
    }

    /**
     * Relación con el Período / Lapso.
     */
    public function lapso()
    {
        return $this->belongsTo(Lapso::class, 'id_lapso', 'id');
    }

    /**
     * Relación con el Administrador que verificó la solicitud.
     */
    public function verificador()
    {
        return $this->belongsTo(Usuario::class, 'verificado_por', 'id_usuario');
    }
}
