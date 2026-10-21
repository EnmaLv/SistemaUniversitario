<?php

namespace App\Models;

use App\Traits\ConvierteAMayusculasNoEloquent;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusMarca extends Model
{
    use ConvierteAMayusculasNoEloquent;
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'marca_vehiculo';

    protected $primaryKey = 'id_mav';

    protected $fillable = ['nombre_mav', 'descripcion_mav', 'status_id'];

    public function modelos()
    {
        return $this->hasMany(BusModelo::class, 'mav_id', 'id_mav');
    }

    public static function listarMarcas($buscar = null, $estado = 1)
    {
        return self::query()
            ->when($buscar, fn ($q) => $q->where('nombre_mav', 'like', "%{$buscar}%"))
            ->when($estado !== null && $estado !== '', fn ($q) => $q->where('status_id', (int) $estado === 1 ? self::STATUS_ACTIVO : self::STATUS_INACTIVO))
            ->orderBy('nombre_mav')
            ->paginate(10)
            ->withQueryString();
    }

    public static function crearMarca(array $datos)
    {
        return self::create([
            'nombre_mav'      => $datos['nombre'],
            'descripcion_mav' => $datos['descripcion'] ?? null,
            'status_id'       => self::STATUS_ACTIVO,
        ]);
    }

    public static function actualizarMarca(BusMarca $marca, array $datos)
    {
        $marca->update([
            'nombre_mav'      => $datos['nombre'],
            'descripcion_mav' => $datos['descripcion'] ?? null,
        ]);

        return $marca;
    }

    public function getDescripcionMavAttribute($value): string
    {
        return $value ?? 'Ninguna';
    }
}
