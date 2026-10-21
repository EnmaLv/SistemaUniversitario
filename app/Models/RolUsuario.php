<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolUsuario extends Model
{
    protected $table = 'rol_usuario';

    protected $fillable = [
        'id_rol',
        'id_usuario',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    /**
     * Nombre completo de la persona asociada (o el username si no tiene persona).
     */
    public function getNombreCompletoAttribute(): string
    {
        $persona = optional($this->usuario)->persona;

        if ($persona) {
            $nombre = "{$persona->nombres_per} {$persona->apellidos_per}";
            if ($nombre !== '') {
                return $nombre;
            }
        }

        return optional($this->usuario)->username ?? 'Usuario sin nombre';
    }

    /**
     * Nombre del rol asociado a esta fila.
     */
    public function getNombreRolAttribute(): string
    {
        return optional($this->rol)->nombre ?? 'Sin Rol';
    }
}