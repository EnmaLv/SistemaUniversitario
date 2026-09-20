<?php

namespace App\Models\Becas;

use Illuminate\Database\Eloquent\Model;

class Beca extends Model
{
    protected $table = 'be_becas';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'activo',
        'requiere_tutor',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'requiere_tutor' => 'boolean',
    ];
}
