<?php

namespace App\Models;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permiso extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'permiso';

    protected $primaryKey = 'id_pmi';

    protected $fillable = [
        'nombre_pmi',
        'etiqueta_pmi',
        'descripcion_pmi',
        'accion_pmi',
        'vis_id',
        'mod_id',
        'status_id',
    ];

    public function vista()
    {
        return $this->belongsTo(Vista::class, 'vis_id', 'id_vis');
    }

    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'mod_id', 'id_mod');
    }

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'rol_permiso', 'pmi_id', 'rol_id')->withTimestamps();
    }

    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'usuario_permiso', 'pmi_id', 'use_id')
            ->withPivot(['tipo_usp', 'fecha_inicio_usp', 'fecha_fin_usp', 'asignado_por_use_id'])
            ->withTimestamps();
    }
}
