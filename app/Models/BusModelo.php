<?php

namespace App\Models;

use App\Traits\ConvierteAMayusculasNoEloquent;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusModelo extends Model
{
    use ConvierteAMayusculasNoEloquent;
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'modelo_vehiculo';

    protected $primaryKey = 'id_mov';

    protected $fillable = ['mav_id', 'nombre_mov', 'descripcion_mov', 'status_id'];

    public function busMarca()
    {
        return $this->belongsTo(BusMarca::class, 'mav_id', 'id_mav');
    }

    public function vehiculos()
    {
        return $this->hasMany(BusVehiculo::class, 'mov_id', 'id_mov');
    }

    public function getDescripcionMovAttribute($value): string
    {
        return $value ?? 'Ninguna';
    }

    public static function listarModelos($buscar = null, $estado = 1)
    {
        return self::query()
            ->with('busMarca')
            ->when($buscar, fn ($q) => $q->where('nombre_mov', 'like', "%{$buscar}%"))
            ->when($estado !== null && $estado !== '', fn ($q) => $q->where('status_id', (int) $estado === 1 ? self::STATUS_ACTIVO : self::STATUS_INACTIVO))
            ->orderBy('nombre_mov')
            ->paginate(10)
            ->withQueryString();
    }

    public static function crearModelo(array $datos)
    {
        return self::create([
            'mav_id'          => $datos['marca_id'],
            'nombre_mov'      => $datos['nombre'],
            'descripcion_mov' => $datos['descripcion'] ?? null,
            'status_id'       => self::STATUS_ACTIVO,
        ]);
    }

    public static function actualizarModelo(BusModelo $modelo, array $datos)
    {
        $modelo->update([
            'mav_id'          => $datos['marca_id'],
            'nombre_mov'      => $datos['nombre'],
            'descripcion_mov' => $datos['descripcion'] ?? null,
        ]);

        return $modelo;
    }
}
