<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolUsuario extends Model
{
    protected $table = 'usuario_rol';

    protected $primaryKey = 'id_usr';

    protected $fillable = [
        'rol_id',
        'use_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'use_id', 'id_use');
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id', 'id_rol');
    }

    public function getNombreCompletoAttribute(): string
    {
        $persona = optional($this->usuario)->persona;

        if ($persona) {
            $nombre = "{$persona->nombres_per} {$persona->apellidos_per}";
            if ($nombre !== '') {
                return $nombre;
            }
        }

        return optional($this->usuario)->username_use ?? 'Usuario sin nombre';
    }

    public function getNombreRolAttribute(): string
    {
        return optional($this->rol)->nombre_rol ?? 'Sin Rol';
    }
}
