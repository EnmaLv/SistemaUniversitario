<?php

namespace App\Models\Deporte;

use App\Models\Persona;
use App\Traits\PerteneceASedePorRelacion;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IntegranteEquipo extends Model
{
    use PerteneceASedePorRelacion;
    use RegistraAutoria;
    use SoftDeletes;

    protected $relacionSede = 'equipo';

    protected $table = 'integrante_equipo_deporte';

    protected $primaryKey = 'id_ied';

    protected $fillable = [
        'etd_id',
        'per_id',
        'status_id',
    ];

    protected $casts = [
        'status_id' => 'integer',
    ];

    public function equipo()
    {
        return $this->belongsTo(EquipoTorneo::class, 'etd_id', 'id_etd');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'per_id', 'id_per');
    }
}
