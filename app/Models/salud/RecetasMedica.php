<?php

namespace App\Models\salud;

use App\Models\Persona;
use App\Traits\PerteneceASedePorRelacion;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecetasMedica extends Model
{
    use PerteneceASedePorRelacion;
    use RegistraAutoria;
    use SoftDeletes;

    protected $relacionSede = 'consulta';

    protected $table = 'receta_medica';

    protected $primaryKey = 'id_rme';

    protected $fillable = [
        'cme_id',
        'paciente_per_id',
        'medico_per_id',
        'fecha_emision_rme',
        'descripcion_rme',
        'fecha_vigencia_rme',
        'estado_rme',
        'status_id',
    ];

    protected $casts = [
        'fecha_emision_rme'  => 'date',
        'fecha_vigencia_rme' => 'date',
        'estado_rme'         => 'integer',
    ];

    public function consulta()
    {
        return $this->belongsTo(Consulta::class, 'cme_id', 'id_cme');
    }

    public function paciente()
    {
        return $this->belongsTo(Persona::class, 'paciente_per_id', 'id_per');
    }

    public function medico()
    {
        return $this->belongsTo(Persona::class, 'medico_per_id', 'id_per');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleRecetasMedica::class, 'rme_id', 'id_rme');
    }

    public function dispensaciones()
    {
        return $this->hasMany(Dispensacion::class, 'rme_id', 'id_rme');
    }

    public function tieneItemsPendientes(): bool
    {
        return $this->detalles->contains(fn ($d) => $d->cantidad_pendiente > 0);
    }

    public function estaCompletamenteDispensada(): bool
    {
        return !$this->tieneItemsPendientes();
    }
}
