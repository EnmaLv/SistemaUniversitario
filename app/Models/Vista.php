<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vista extends Model
{
    protected $table = 'vista';

    protected $fillable = [
        'key',
        'nombre',
        'descripcion',
        'ruta',
        'icono',
        'orden',
        'modulo_id',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden'  => 'integer',
    ];

    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'modulo_id');
    }

    public function permisos()
    {
        return $this->hasMany(Permiso::class, 'vista_id');
    }
}
