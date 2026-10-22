<?php

namespace App\Models;

use App\Traits\ConvierteAMayusculasNoEloquent;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusTipoCombustible extends Model
{
    use ConvierteAMayusculasNoEloquent;
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'tipo_combustible';

    protected $primaryKey = 'id_tcb';

    protected $fillable = ['nombre_tcb', 'descripcion_tcb', 'status_id'];

    public function vehiculos()
    {
        return $this->hasMany(BusVehiculo::class, 'tcb_id', 'id_tcb');
    }

    public static function listarTipos($buscar = null, $estado = 1)
    {
        return self::query()
            ->when($buscar, fn ($q) => $q->where('nombre_tcb', 'like', "%{$buscar}%"))
            ->when($estado !== null && $estado !== '', fn ($q) => $q->where('status_id', (int) $estado === 1 ? self::STATUS_ACTIVO : self::STATUS_INACTIVO))
            ->orderBy('nombre_tcb')
            ->paginate(10)
            ->withQueryString();
    }

    public static function crearTipo(array $datos)
    {
        return self::create([
            'nombre_tcb'      => $datos['nombre'],
            'descripcion_tcb' => $datos['descripcion'] ?? null,
            'status_id'       => self::STATUS_ACTIVO,
        ]);
    }

    public static function actualizarTipo(BusTipoCombustible $tipo, array $datos)
    {
        $tipo->update([
            'nombre_tcb'      => $datos['nombre'],
            'descripcion_tcb' => $datos['descripcion'] ?? null,
        ]);

        return $tipo;
    }
}
