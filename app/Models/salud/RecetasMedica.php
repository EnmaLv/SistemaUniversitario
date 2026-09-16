<?php

namespace App\Models\salud;

use App\Models\Persona;
use Illuminate\Database\Eloquent\Model;

class RecetasMedica extends Model
{
    protected $table = 'recetas_medicas';

    protected $fillable = [
        'consulta_id',
        'id_persona',
        'medico_id',
        'fecha',
        'descripcion',
        'vigencia',
        'estado',
    ];

    protected $casts = [
        'fecha'    => 'date',
        'vigencia' => 'date',
        'estado'   => 'integer',
    ];

    public const ESTADO_VIGENTE = 1;
    public const ESTADO_ANULADA = 0;

    public function consulta()
    {
        return $this->belongsTo(Consulta::class, 'consulta_id');
    }

    public function paciente()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    public function medico()
    {
        return $this->belongsTo(Persona::class, 'medico_id', 'id_persona');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleRecetasMedica::class, 'receta_id');
    }

    /**
     * ¿Queda al menos un medicamento con saldo por dispensar?
     */
    public function tieneItemsPendientes(): bool
    {
        return $this->detalles->contains(fn ($d) => $d->cantidad_pendiente > 0);
    }

    public function estaCompletamenteDispensada(): bool
    {
        return !$this->tieneItemsPendientes();
    }
}