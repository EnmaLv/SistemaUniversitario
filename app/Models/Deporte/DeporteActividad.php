<?php

namespace App\Models\Deporte;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeporteActividad extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    public const TIPOS = [
        'programada' => 'Programada',
        'libre'      => 'Libre',
        'torneo'     => 'Torneo',
    ];

    protected $table = 'actividad_deportiva';

    protected $primaryKey = 'id_acd';

    protected $fillable = [
        'nombre_acd',
        'descripcion_acd',
        'tipo_acd',
        'status_id',
    ];

    protected $casts = [
        'status_id' => 'integer',
    ];

    public function horarios()
    {
        return $this->hasMany(DeporteHorario::class, 'acd_id', 'id_acd');
    }

    public function tipoLegible(): string
    {
        return self::TIPOS[$this->tipo_acd] ?? ucfirst((string) $this->tipo_acd);
    }
}
