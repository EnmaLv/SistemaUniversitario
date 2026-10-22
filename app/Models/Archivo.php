<?php

namespace App\Models;

use App\Traits\ConvierteAMayusculas;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Archivo extends Model
{
    use ConvierteAMayusculas;
    use HasFactory;
    protected $table = 'archivos';

    protected $fillable = [
        'info_estudiantes',
        'ruta_archivo',
        'nombre_original',
        'resumen',
        'usuario_id',
        'fecha',
        'estado',
    ];

    protected $casts = [
        'resumen' => 'array',
    ];

    protected $mayusculas = [
        'info_estudiantes'
    ];
}
