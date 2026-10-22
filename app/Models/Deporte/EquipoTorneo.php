<?php

namespace App\Models\Deporte;

use App\Models\Pnf;
use App\Traits\PerteneceASedePorRelacion;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipoTorneo extends Model
{
    use PerteneceASedePorRelacion;
    use RegistraAutoria;
    use SoftDeletes;

    public const ESTADOS = [
        'inscrito'  => 'Inscrito',
        'eliminado' => 'Eliminado',
        'campeon'   => 'Campeón',
    ];

    protected $relacionSede = 'torneo';

    protected $table = 'equipo_torneo_deportivo';

    protected $primaryKey = 'id_etd';

    protected $fillable = [
        'nombre_equipo_etd',
        'estado_etd',
        'tde_id',
        'prg_id',
        'status_id',
    ];

    protected $casts = [
        'status_id' => 'integer',
    ];

    public function torneo()
    {
        return $this->belongsTo(TorneoDeportivo::class, 'tde_id', 'id_tde');
    }

    public function programa()
    {
        return $this->belongsTo(Pnf::class, 'prg_id', 'id_prg');
    }

    public function integrantes()
    {
        return $this->hasMany(IntegranteEquipo::class, 'etd_id', 'id_etd');
    }
}
