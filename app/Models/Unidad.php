<?php

namespace App\Models;

use App\Traits\ConvierteAMayusculas;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unidad extends Model
{
    use ConvierteAMayusculas;
    use HasFactory;
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'unidad_medida';

    protected $primaryKey = 'id_unm';

    protected $fillable = [
        'nombre_unm',
        'abreviatura_unm',
        'factor_conversion_unm',
        'status_id',
    ];

    protected $mayusculas = [
        'nombre_unm',
        'abreviatura_unm',
    ];

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'unm_id', 'id_unm');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'unm_id', 'id_unm');
    }

    public function recetaIngredientes()
    {
        return $this->hasMany(RecetaIngrediente::class, 'unm_id', 'id_unm');
    }
}
