<?php

namespace App\Models\Deporte;

use App\Models\Sede;
use App\Traits\PerteneceASede;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeporteEspacio extends Model
{
    use PerteneceASede;
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'espacio_deportivo';

    protected $primaryKey = 'id_epd';

    protected $fillable = [
        'nombre_epd',
        'descripcion_epd',
        'ubicacion_epd',
        'sed_id',
        'status_id',
    ];

    protected $casts = [
        'sed_id'    => 'integer',
        'status_id' => 'integer',
    ];

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sed_id', 'id_sed');
    }

    public function horarios()
    {
        return $this->hasMany(DeporteHorario::class, 'epd_id', 'id_epd');
    }
}
