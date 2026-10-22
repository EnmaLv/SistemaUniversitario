<?php

namespace App\Models\Deporte;

use App\Models\Sede;
use App\Models\Usuario;
use App\Traits\PerteneceASede;
use App\Traits\RegistraAutoria;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeporteCalendario extends Model
{
    use PerteneceASede;
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'calendario_deportivo';

    protected $primaryKey = 'id_cld';

    protected $fillable = [
        'nombre_cld',
        'descripcion_cld',
        'fecha_inicio_cld',
        'fecha_fin_cld',
        'sed_id',
        'use_id',
        'status_id',
    ];

    protected $casts = [
        'fecha_inicio_cld' => 'date',
        'fecha_fin_cld'    => 'date',
        'sed_id'           => 'integer',
        'status_id'        => 'integer',
    ];

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sed_id', 'id_sed');
    }

    public function coordinador()
    {
        return $this->belongsTo(Usuario::class, 'use_id', 'id_use');
    }

    public function horarios()
    {
        return $this->hasMany(DeporteHorario::class, 'cld_id', 'id_cld');
    }

    public function contieneFecha($fecha): bool
    {
        $fecha = Carbon::parse($fecha)->startOfDay();

        return $fecha->betweenIncluded($this->fecha_inicio_cld->copy()->startOfDay(), $this->fecha_fin_cld->copy()->startOfDay());
    }
}
