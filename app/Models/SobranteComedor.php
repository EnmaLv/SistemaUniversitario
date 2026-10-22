<?php

namespace App\Models;

use App\Traits\ConvierteAMayusculas;
use App\Traits\PerteneceASede;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SobranteComedor extends Model
{
    use ConvierteAMayusculas;
    use HasFactory;
    use PerteneceASede;
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'sobrante_comedor';

    protected $primaryKey = 'id_sbc';

    protected $fillable = [
        'sed_id',
        'fecha_sbc',
        'cantidad_sobrante_sbc',
        'motivo_sbc',
        'accion_tomada_sbc',
    ];

    protected $mayusculas = [
        'motivo_sbc',
        'accion_tomada_sbc',
    ];
}
