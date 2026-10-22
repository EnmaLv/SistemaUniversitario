<?php

namespace App\Models\Deporte;

use App\Models\Usuario;
use App\Traits\PerteneceASedePorRelacion;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TorneoDeportivo extends Model
{
    use PerteneceASedePorRelacion;
    use RegistraAutoria;
    use SoftDeletes;

    public const ESTADO_ABIERTO = 'abierto';

    public const ESTADO_EN_CURSO = 'en_curso';

    public const ESTADO_FINALIZADO = 'finalizado';

    public const ESTADO_CANCELADO = 'cancelado';

    public const ESTADOS = [
        self::ESTADO_ABIERTO    => 'Abierto',
        self::ESTADO_EN_CURSO   => 'En curso',
        self::ESTADO_FINALIZADO => 'Finalizado',
        self::ESTADO_CANCELADO  => 'Cancelado',
    ];

    public const ESTADOS_VIGENTES = [self::ESTADO_ABIERTO, self::ESTADO_EN_CURSO];

    public const MODALIDADES = ['Masculino', 'Femenino', 'Mixto'];

    protected $relacionSede = 'horario';

    protected $table = 'torneo_deportivo';

    protected $primaryKey = 'id_tde';

    protected $fillable = [
        'nombre_tde',
        'descripcion_tde',
        'modalidad_tde',
        'estado_tde',
        'hde_id',
        'use_id',
        'status_id',
    ];

    protected $casts = [
        'status_id' => 'integer',
    ];

    public function horario()
    {
        return $this->belongsTo(DeporteHorario::class, 'hde_id', 'id_hde');
    }

    public function coordinador()
    {
        return $this->belongsTo(Usuario::class, 'use_id', 'id_use');
    }

    public function equipos()
    {
        return $this->hasMany(EquipoTorneo::class, 'tde_id', 'id_tde');
    }

    public function estadoLegible(): string
    {
        return self::ESTADOS[$this->estado_tde] ?? ucfirst((string) $this->estado_tde);
    }

    public function admiteInscripciones(): bool
    {
        return $this->estado_tde === self::ESTADO_ABIERTO;
    }

    public function estaVigente(): bool
    {
        return in_array($this->estado_tde, self::ESTADOS_VIGENTES, true);
    }
}
