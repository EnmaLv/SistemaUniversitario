<?php

namespace App\Models;

use App\Traits\PerteneceASedePorRelacion;
use App\Traits\RegistraAutoria;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusViaje extends Model
{
    use PerteneceASedePorRelacion;
    use RegistraAutoria;
    use SoftDeletes;

    protected $relacionSede = 'vehiculo';

    protected $table = 'viaje_transporte';

    protected $primaryKey = 'id_vjt';

    protected $fillable = [
        'vtr_id',
        'rtr_id',
        'conductor_use_id',
        'turno_vjt',
        'firebase_id_vjt',
        'fecha_inicio_vjt',
        'km_inicio_vjt',
        'km_fin_vjt',
        'distancia_km_vjt',
        'litros_gastados_vjt',
        'total_pasajeros_vjt',
        'hubo_desvio_vjt',
        'motivo_desvio_vjt',
        'estado_viaje_vjt',
        'status_id',
    ];

    protected $casts = [
        'fecha_inicio_vjt'    => 'datetime',
        'hubo_desvio_vjt'     => 'boolean',
        'km_inicio_vjt'       => 'decimal:2',
        'km_fin_vjt'          => 'decimal:2',
        'distancia_km_vjt'    => 'decimal:2',
        'litros_gastados_vjt' => 'decimal:2',
        'total_pasajeros_vjt' => 'integer',
    ];

    public function gpsLogs()
    {
        return $this->hasMany(BusGpsLog::class, 'vjt_id', 'id_vjt');
    }

    public function vehiculo()
    {
        return $this->belongsTo(BusVehiculo::class, 'vtr_id', 'id_vtr');
    }

    public function ruta()
    {
        return $this->belongsTo(BusRuta::class, 'rtr_id', 'id_rtr');
    }

    public function busRuta()
    {
        return $this->belongsTo(BusRuta::class, 'rtr_id', 'id_rtr');
    }

    public function conductor()
    {
        return $this->belongsTo(Usuario::class, 'conductor_use_id', 'id_use');
    }

    public function cargasCombustible()
    {
        return $this->hasMany(BusCargaCombustible::class, 'vjt_id', 'id_vjt');
    }

    public function pasajerosRegistrados()
    {
        return $this->hasMany(ViajePasajero::class, 'vjt_id', 'id_vjt');
    }

    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado_viaje_vjt) {
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
            ->when($buscar, fn ($q) => $q->where(fn ($sub) => $sub
                ->whereHas('vehiculo', fn ($q2) => $q2->where('placa_vtr', 'like', "%{$buscar}%"))
                ->orWhereHas('ruta', fn ($q2) => $q2->where('nombre_rtr', 'like', "%{$buscar}%"))))
            ->when($estado !== null && $estado !== '', fn ($q) => $q->where('estado_viaje_vjt', $estado))
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
        return $query->where($this->qualifyColumn('conductor_use_id'), $conductorId);
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
