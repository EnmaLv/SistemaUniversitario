<?php

namespace App\Models;

use App\Traits\PerteneceASede;
use App\Traits\ConvierteAMayusculas;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SobranteComedor extends Model
{
    use PerteneceASede;

    use ConvierteAMayusculas;
    use HasFactory;

    protected $table = 'sobrantes_comedor';

    protected $fillable = [
        'fecha',
        'cantidad_sobrante',
        'motivo',
        'accion_tomada',
    ];

    protected $mayusculas = [
        'motivo',
        'accion_tomada',
    ];
}
