<?php

namespace App\Models;

use App\Traits\ConvierteAMayusculasNoEloquent;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusParada extends Model
{
    use ConvierteAMayusculasNoEloquent;
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'parada_transporte';

    protected $primaryKey = 'id_ptr';

    protected $fillable = ['nombre_ptr', 'lat_ptr', 'lng_ptr', 'direccion_ptr', 'status_id'];

    public function rutas()
    {
        return $this->belongsToMany(BusRuta::class, 'ruta_parada_transporte', 'ptr_id', 'rtr_id', 'id_ptr', 'id_rtr')
            ->withPivot('orden_rpt')
            ->withTimestamps()
            ->wherePivotNull('deleted_at');
    }

    public function getDireccionPtrAttribute($value): string
    {
        return $value ?? 'Ninguna';
    }

    public static function listarParadas($buscar = null, $estado = 1)
    {
        return self::query()
            ->when($buscar, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('nombre_ptr', 'like', "%{$buscar}%")
                ->orWhere('direccion_ptr', 'like', "%{$buscar}%")))
            ->when($estado !== null && $estado !== '', fn ($q) => $q->where('status_id', (int) $estado === 1 ? self::STATUS_ACTIVO : self::STATUS_INACTIVO))
            ->orderBy('nombre_ptr')
            ->paginate(10)
            ->withQueryString();
    }

    public static function crearParada(array $datos)
    {
        return self::create([
            'nombre_ptr'    => $datos['nombre'],
            'lat_ptr'       => $datos['lat'] ?? null,
            'lng_ptr'       => $datos['lng'] ?? null,
            'direccion_ptr' => $datos['direccion'] ?? null,
            'status_id'     => self::STATUS_ACTIVO,
        ]);
    }

    public static function actualizarParada(BusParada $parada, array $datos)
    {
        $parada->update([
            'nombre_ptr'    => $datos['nombre'],
            'lat_ptr'       => $datos['lat'] ?? null,
            'lng_ptr'       => $datos['lng'] ?? null,
            'direccion_ptr' => $datos['direccion'] ?? null,
        ]);

        return $parada;
    }
}
