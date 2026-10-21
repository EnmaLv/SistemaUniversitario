<?php

namespace App\Models;

use App\Traits\PerteneceASedePorRelacion;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class BusCargaCombustible extends Model
{
    use PerteneceASedePorRelacion;
    use RegistraAutoria;
    use SoftDeletes;

    public const COLUMNAS_FORMULARIO = [
        'bus_vehiculo_id'         => 'vtr_id',
        'bus_viaje_id'            => 'vjt_id',
        'bus_tipo_combustible_id' => 'tcb_id',
        'fecha'                   => 'fecha_ccb',
        'litros'                  => 'litros_ccb',
        'precio_litros'           => 'precio_litro_ccb',
        'km_al_cargar'            => 'km_al_cargar_ccb',
        'observaciones'           => 'observaciones_ccb',
    ];

    protected $relacionSede = 'vehiculo';

    protected $table = 'carga_combustible';

    protected $primaryKey = 'id_ccb';

    protected $fillable = [
        'vtr_id',
        'vjt_id',
        'tcb_id',
        'fecha_ccb',
        'litros_ccb',
        'precio_litro_ccb',
        'total_ccb',
        'km_al_cargar_ccb',
        'observaciones_ccb',
        'status_id',
    ];

    protected $casts = [
        'fecha_ccb'        => 'date',
        'litros_ccb'       => 'decimal:2',
        'precio_litro_ccb' => 'decimal:2',
        'total_ccb'        => 'decimal:2',
        'km_al_cargar_ccb' => 'decimal:2',
    ];

    public function vehiculo()
    {
        return $this->belongsTo(BusVehiculo::class, 'vtr_id', 'id_vtr');
    }

    public function viaje()
    {
        return $this->belongsTo(BusViaje::class, 'vjt_id', 'id_vjt');
    }

    public function tipoCombustible()
    {
        return $this->belongsTo(BusTipoCombustible::class, 'tcb_id', 'id_tcb');
    }

    public static function columnasDesdeFormulario(array $datos): array
    {
        $columnas = [];

        foreach (self::COLUMNAS_FORMULARIO as $campo => $columna) {
            if (array_key_exists($campo, $datos)) {
                $columnas[$columna] = $datos[$campo];
            }
        }

        if (isset($columnas['litros_ccb'], $columnas['precio_litro_ccb'])) {
            $columnas['total_ccb'] = $columnas['litros_ccb'] * $columnas['precio_litro_ccb'];
        }

        return $columnas;
    }

    public static function listarCargas($buscar = null, $vehiculoId = null)
    {
        return self::query()
            ->with(['vehiculo', 'viaje.ruta', 'tipoCombustible'])
            ->when($buscar, fn ($q) => $q->where(fn ($sub) => $sub
                ->whereHas('vehiculo', fn ($q2) => $q2->where('placa_vtr', 'like', "%{$buscar}%"))
                ->orWhere('observaciones_ccb', 'like', "%{$buscar}%")))
            ->when($vehiculoId, fn ($q) => $q->where('vtr_id', $vehiculoId))
            ->orderBy('fecha_ccb', 'desc')
            ->paginate(10)
            ->withQueryString();
    }

    public static function crearCarga(array $datos): self
    {
        $columnas = self::columnasDesdeFormulario($datos);

        return DB::transaction(function () use ($columnas) {
            $carga = self::create($columnas);

            $vehiculo = $carga->vehiculo()->lockForUpdate()->first();

            if ($vehiculo) {
                $nuevoNivel = (float) $vehiculo->nivel_combustible_actual_vtr + (float) $carga->litros_ccb;
                $nuevoNivel = min($nuevoNivel, (float) $vehiculo->capacidad_tanque_litros_vtr);

                $vehiculo->update([
                    'nivel_combustible_actual_vtr' => $nuevoNivel,
                    'km_actual_vtr'                => max((float) $vehiculo->km_actual_vtr, (float) $carga->km_al_cargar_ccb),
                ]);
            }

            return $carga;
        });
    }

    public static function actualizarCarga(self $carga, array $datos): self
    {
        $litrosAnteriores = (float) $carga->litros_ccb;
        $columnas = self::columnasDesdeFormulario($datos);

        return DB::transaction(function () use ($carga, $columnas, $litrosAnteriores) {
            $carga->update($columnas);

            $vehiculo = $carga->vehiculo()->lockForUpdate()->first();

            if ($vehiculo) {
                $diferenciaLitros = (float) $columnas['litros_ccb'] - $litrosAnteriores;
                $nuevoNivel = (float) $vehiculo->nivel_combustible_actual_vtr + $diferenciaLitros;
                $nuevoNivel = max(0, min($nuevoNivel, (float) $vehiculo->capacidad_tanque_litros_vtr));

                $vehiculo->update(['nivel_combustible_actual_vtr' => $nuevoNivel]);
            }

            return $carga;
        });
    }
}
