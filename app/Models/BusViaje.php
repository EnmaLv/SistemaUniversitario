<?php

namespace App\Models;

use App\Traits\PerteneceASedePorRelacion;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\BusVehiculo;
use App\Models\BusRuta;
use App\Models\Usuario;

class BusViaje extends Model
{
    use PerteneceASedePorRelacion;

    protected $relacionSede = 'vehiculo';

    protected $table = 'bus_viajes';

    protected $fillable = [
        'vehiculo_id',
        'bus_ruta_id',
        'conductor_id',
        'turno',
        'firebase_id',
        'fecha_inicio',
        'km_inicio',
        'km_fin',
        'distancia_km',
        'litros_gastados',
        'pasajeros',
        'hubo_desvio',
        'motivo_desvio',
        'estado',
    ];

    protected $casts = [
        'fecha_inicio'   => 'datetime',
        'fecha_fin'    => 'datetime',
        'hubo_desvio'    => 'boolean',
        'km_inicio'       => 'decimal:2',
        'km_fin'          => 'decimal:2',
        'distancia_km'   => 'decimal:2',
        'litros_gastados' => 'decimal:2',
        'pasajeros'      => 'integer',
    ];

    public function gpsLogs()
    {
        return $this->hasMany(BusGpsLog::class, 'bus_viaje_id');
    }

    public function vehiculo()
    {
        return $this->belongsTo(BusVehiculo::class, 'vehiculo_id');
    }

    public function ruta()
    {
        return $this->belongsTo(BusRuta::class, 'bus_ruta_id');
    }

    public function busRuta()
    {
        return $this->belongsTo(BusRuta::class, 'bus_ruta_id');
    }

    public function conductor()
    {
        return $this->belongsTo(Usuario::class, 'conductor_id', 'id_use');
    }

    public function cargasCombustible()
    {
        return $this->hasMany(BusCargaCombustible::class, 'bus_viaje_id');
    }

    public function getEstadoBadgeAttribute(): string
    {
        return match($this->estado) {
            'programado' => '<span class="rd-badge rd-badge-warning">Programado</span>',
            'en_curso'   => '<span class="rd-badge rd-badge-info">En Curso</span>',
            'finalizado' => '<span class="rd-badge rd-badge-success">Finalizado</span>',
            'cancelado'  => '<span class="rd-badge rd-badge-danger">Cancelado</span>',
            default      => '<span class="rd-badge">-</span>',
        };
    }

    public static function listarViajes($buscar = null, $estado = null)
    {
        return self::query()
            ->with(['vehiculo', 'ruta', 'conductor'])
            ->when($buscar, fn ($q) => $q
                ->whereHas('vehiculo', fn ($q2) => $q2->where('placa', 'like', "%{$buscar}%"))
                ->orWhereHas('ruta', fn ($q2) => $q2->where('nombre', 'like', "%{$buscar}%")))
            ->when($estado !== null && $estado !== '', fn ($q) => $q->where('estado', $estado))
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();
    }

    public static function crearViaje(array $datos): self
    {
        return self::create($datos);
    }

    public static function actualizarViaje(self $viaje, array $datos): self
    {
        $viaje->update($datos);
        return $viaje;
    }

    public function scopeDelConductor($query, $conductorId)
    {
        return $query->where('conductor_id', $conductorId);
    }

    public static function calcularTurnoActual(): string
    {
        $hora = Carbon::now()->hour;

        if ($hora >= 6 && $hora < 13) {
            return 'mañana';
        } elseif ($hora >= 13 && $hora < 18) {
            return 'tarde';
        } else {
            return 'noche';
        }
    }
}