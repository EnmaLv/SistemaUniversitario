<?php

namespace App\Models;

use App\Traits\ConvierteAMayusculas;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Receta extends Model
{
    use ConvierteAMayusculas;
    use HasFactory;
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'receta';

    protected $primaryKey = 'id_rct';

    protected $fillable = [
        'nombre_rct',
        'descripcion_rct',
        'status_id',
    ];

    protected $mayusculas = [
        'nombre_rct',
        'descripcion_rct',
    ];

    public function recetaIngredientes()
    {
        return $this->hasMany(RecetaIngrediente::class, 'rct_id', 'id_rct');
    }

    public function detalleRegistroDiarios()
    {
        return $this->hasMany(DetalleRegistroDiario::class, 'rct_id', 'id_rct');
    }

    public static function tieneIngredienes($id)
    {
        return DB::table('receta_ingrediente')
            ->where('rct_id', $id)
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->exists();
    }

    public static function cantidadIngredientes($id)
    {
        return DB::table('receta_ingrediente')
            ->where('rct_id', $id)
            ->where('status_id', self::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->count();
    }

    public static function eliminarReceta($id)
    {
        return DB::table('receta')
            ->where('id_rct', $id)
            ->update([
                'status_id'  => self::STATUS_INACTIVO,
                'updated_id' => Auth::id(),
                'updated_at' => now(),
            ]);
    }

    public static function activarReceta($id)
    {
        return DB::table('receta')
            ->where('id_rct', $id)
            ->update([
                'status_id'  => self::STATUS_ACTIVO,
                'updated_id' => Auth::id(),
                'updated_at' => now(),
            ]);
    }
}
