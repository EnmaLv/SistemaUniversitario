<?php

namespace App\Models;

use App\Traits\ConvierteAMayusculasNoEloquent;
use App\Traits\PerteneceASede;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusRuta extends Model
{
    use ConvierteAMayusculasNoEloquent;
    use PerteneceASede;
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'ruta_transporte';

    protected $primaryKey = 'id_rtr';

    protected $fillable = [
        'nombre_rtr',
        'distancia_km_rtr',
        'descripcion_rtr',
        'sed_id',
        'status_id',
    ];

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sed_id', 'id_sed');
    }

    public function horarios()
    {
        return $this->hasMany(RutaHorario::class, 'rtr_id', 'id_rtr');
    }

    public function paradas()
    {
        return $this->belongsToMany(BusParada::class, 'ruta_parada_transporte', 'rtr_id', 'ptr_id', 'id_rtr', 'id_ptr')
            ->withPivot('orden_rpt')
            ->withTimestamps()
            ->wherePivotNull('deleted_at')
            ->orderBy('ruta_parada_transporte.orden_rpt');
    }

    public function rutaParadas()
    {
        return $this->hasMany(BusRutaParada::class, 'rtr_id', 'id_rtr')->orderBy('orden_rpt');
    }

    public function viajes()
    {
        return $this->hasMany(BusViaje::class, 'rtr_id', 'id_rtr');
    }

    public function trazados()
    {
        return $this->hasMany(BusTrazadoRuta::class, 'rtr_id', 'id_rtr');
    }

    public function getDescripcionRtrAttribute($value): string
    {
        return $value ?? 'Ninguna';
    }

    public static function listarRutas($buscar = null, $estado = 1)
    {
        return self::query()
            ->with(['sede', 'horarios', 'paradas'])
            ->when($buscar, fn ($q) => $q->where('nombre_rtr', 'like', "%{$buscar}%"))
            ->when($estado !== null && $estado !== '', fn ($q) => $q->where('status_id', (int) $estado === 1 ? self::STATUS_ACTIVO : self::STATUS_INACTIVO))
            ->orderBy('nombre_rtr')
            ->paginate(10)
            ->withQueryString();
    }

    public static function crearRuta(array $datos)
    {
        return self::create([
            'nombre_rtr'       => $datos['nombre'],
            'distancia_km_rtr' => $datos['distancia_km'],
            'descripcion_rtr'  => $datos['descripcion'] ?? null,
            'sed_id'           => $datos['sed_id'],
            'status_id'        => self::STATUS_ACTIVO,
        ]);
    }

    public static function actualizarRuta(BusRuta $ruta, array $datos)
    {
        $ruta->update([
            'nombre_rtr'       => $datos['nombre'],
            'distancia_km_rtr' => $datos['distancia_km'],
            'descripcion_rtr'  => $datos['descripcion'] ?? null,
            'sed_id'           => $datos['sed_id'],
        ]);

        return $ruta;
    }
}
