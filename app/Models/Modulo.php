<?php

namespace App\Models;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Modulo extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'modulo';

    protected $primaryKey = 'id_mod';

    protected $fillable = ['key_mod', 'nombre_mod', 'descripcion_mod', 'icono_mod', 'orden_mod', 'status_id'];

    public function roles()
    {
        return $this->belongsToMany(\App\Models\Rol::class, 'rol_modulo', 'mod_id', 'rol_id');
    }

    public function vistas()
    {
        return $this->hasMany(Vista::class, 'mod_id', 'id_mod');
    }

    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'usuario_modulo', 'mod_id', 'use_id')->withTimestamps();
    }
}
