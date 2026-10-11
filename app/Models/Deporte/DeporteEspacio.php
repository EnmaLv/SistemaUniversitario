<?php

namespace App\Models\Deporte;

use Illuminate\Database\Eloquent\Model;

class DeporteEspacio extends Model
{
    protected $table = 'deporte_espacio';
    protected $fillable = ['nombre', 'descripcion', 'ubicacion', 'activo'];
    protected $casts = ['activo' => 'integer'];

    public function horarios()
    {
        return $this->hasMany(DeporteHorario::class, 'id_espacio');
    }
}
