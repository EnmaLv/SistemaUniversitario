<?php

namespace App\Models;

use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class RecetaIngrediente extends Model
{
    use HasFactory;
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'receta_ingrediente';

    protected $primaryKey = 'id_rci';

    protected $fillable = [
        'rct_id',
        'pdt_id',
        'cantidad_porcion_rci',
        'cantidad_convertida_rci',
        'unm_id',
        'status_id',
    ];

    public function receta()
    {
        return $this->belongsTo(Receta::class, 'rct_id', 'id_rct');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'pdt_id', 'id_pdt');
    }

    public function unidad()
    {
        return $this->belongsTo(Unidad::class, 'unm_id', 'id_unm');
    }

    public static function eliminarIngredientes($id)
    {
        return DB::table('receta_ingrediente')
            ->where('id_rci', $id)
            ->delete();
    }
}
