<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BusVehiculo;
use App\Models\BusViaje;
use Illuminate\Support\Facades\DB;

class BusCargaCombustible extends Model
{
    protected $table = 'carga_combustibles';

    protected $fillable = [
        'vehiculo_id',
        'bus_viaje_id',
        'fecha',
        'litros',
        'precio_litros',
        'total',
        'km_al_cargar',
        'observaciones',
    ];

    protected $casts = [
        'fecha'         => 'date',
        'litros'        => 'decimal:2',
        'precio_litros' => 'decimal:2',
        'total'         => 'decimal:2',
        'km_al_cargar'  => 'decimal:2',
    ];

    public function vehiculo()
    {
        return $this->belongsTo(BusVehiculo::class, 'vehiculo_id');
    }

    public function viaje()
    {
        return $this->belongsTo(BusViaje::class, 'bus_viaje_id');
    }

    public static function listarCargas($buscar = null, $vehiculoId = null)
    {
        return self::query()
            ->with(['vehiculo', 'viaje.ruta'])
            ->when($buscar, fn ($q) => $q
                ->whereHas('vehiculo', fn ($q2) => $q2->where('placa', 'like', "%{$buscar}%"))
                ->orWhere('observaciones', 'like', "%{$buscar}%"))
            ->when($vehiculoId, fn ($q) => $q->where('vehiculo_id', $vehiculoId))
            ->orderBy('fecha', 'desc')
            ->paginate(10)
            ->withQueryString();
    }

    public static function crearCarga(array $datos): self
    {
        $datos['total'] = $datos['litros'] * $datos['precio_litros'];

        return DB::transaction(function () use ($datos) {
            $carga = self::create($datos);

            $vehiculo = $carga->vehiculo()->lockForUpdate()->first();

            if ($vehiculo) {
                $nuevoNivel = (float) $vehiculo->nivel_combustible_actual + (float) $carga->litros;
                $nuevoNivel = min($nuevoNivel, (float) $vehiculo->capacidad_tanque_litros);

                $vehiculo->update([
                    'nivel_combustible_actual' => $nuevoNivel,
                    'km_actual'                => max((float) $vehiculo->km_actual, (float) $carga->km_al_cargar),
                ]);
            }

            return $carga;
        });
    }

    public static function actualizarCarga(self $carga, array $datos): self
    {
        $litrosAnteriores = (float) $carga->litros;
        $datos['total'] = $datos['litros'] * $datos['precio_litros'];

        return DB::transaction(function () use ($carga, $datos, $litrosAnteriores) {
            $carga->update($datos);

            $vehiculo = $carga->vehiculo()->lockForUpdate()->first();

            if ($vehiculo) {
                $diferenciaLitros = (float) $datos['litros'] - $litrosAnteriores;
                $nuevoNivel = (float) $vehiculo->nivel_combustible_actual + $diferenciaLitros;
                $nuevoNivel = max(0, min($nuevoNivel, (float) $vehiculo->capacidad_tanque_litros));

                $vehiculo->update(['nivel_combustible_actual' => $nuevoNivel]);
            }

            return $carga;
        });
    }
}