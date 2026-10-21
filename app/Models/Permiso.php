<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    protected $table = 'permiso';

    protected $fillable = [
        'key',
        'nombre',
        'descripcion',
        'accion',
        'vista_id',
        'modulo_id',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function vista()
    {
        return $this->belongsTo(Vista::class, 'vista_id');
    }

    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'modulo_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'rol_permiso', 'permiso_id', 'rol_id')->withTimestamps();
    }

    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'usuario_permiso', 'permiso_id', 'usuario_id')
            ->withPivot(['tipo', 'fecha_inicio', 'fecha_fin', 'asignado_por'])
            ->withTimestamps();
    }
}
