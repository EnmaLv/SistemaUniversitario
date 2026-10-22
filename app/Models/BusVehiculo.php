<?php

namespace App\Models;

use App\Traits\ConvierteAMayusculasNoEloquent;
use App\Traits\PerteneceASede;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusVehiculo extends Model
{
    use ConvierteAMayusculasNoEloquent;
    use PerteneceASede;
    use RegistraAutoria;
    use SoftDeletes;

    public const COLUMNAS_FORMULARIO = [
        'placa'                    => 'placa_vtr',
        'modelo_id'                => 'mov_id',
        'anio'                     => 'anio_vtr',
        'color'                    => 'color_vtr',
        'cantidad_pasajeros'       => 'capacidad_pasajeros_vtr',
        'peso'                     => 'peso_vtr',
        'tipo_combustible_id'      => 'tcb_id',
        'cantidad_cilindros'       => 'cantidad_cilindros_vtr',
        'capacidad_tanque_litros'  => 'capacidad_tanque_litros_vtr',
        'nivel_combustible_actual' => 'nivel_combustible_actual_vtr',
        'consumo_urbano'           => 'consumo_urbano_vtr',
        'consumo_carretera'        => 'consumo_carretera_vtr',
        'consumo_relenti'          => 'consumo_ralenti_vtr',
        'km_actual'                => 'km_actual_vtr',
        'km_proximo_mantenimiento' => 'km_proximo_mantenimiento_vtr',
        'estado'                   => 'estado_operativo_vtr',
        'sed_id'                   => 'sed_id',
    ];

    protected $table = 'vehiculo_transporte';

    protected $primaryKey = 'id_vtr';

    protected $fillable = [
        'placa_vtr',
        'mov_id',
        'anio_vtr',
        'color_vtr',
        'capacidad_pasajeros_vtr',
        'peso_vtr',
        'tcb_id',
        'cantidad_cilindros_vtr',
        'capacidad_tanque_litros_vtr',
        'nivel_combustible_actual_vtr',
        'consumo_urbano_vtr',
        'consumo_carretera_vtr',
        'consumo_ralenti_vtr',
        'km_actual_vtr',
        'km_proximo_mantenimiento_vtr',
        'estado_operativo_vtr',
        'sed_id',
        'status_id',
    ];

    protected $casts = [
        'consumo_urbano_vtr'           => 'decimal:3',
        'consumo_carretera_vtr'        => 'decimal:3',
        'consumo_ralenti_vtr'          => 'decimal:3',
        'capacidad_tanque_litros_vtr'  => 'decimal:2',
        'km_actual_vtr'                => 'decimal:2',
        'km_proximo_mantenimiento_vtr' => 'decimal:2',
    ];

    public function modelo()
    {
        return $this->belongsTo(BusModelo::class, 'mov_id', 'id_mov');
    }

    public function tipoCombustible()
    {
        return $this->belongsTo(BusTipoCombustible::class, 'tcb_id', 'id_tcb');
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sed_id', 'id_sed');
    }

    public function viajes()
    {
        return $this->hasMany(BusViaje::class, 'vtr_id', 'id_vtr');
    }

    public function conductor()
    {
        return $this->hasOneThrough(Usuario::class, BusViaje::class, 'vtr_id', 'id_use', 'id_vtr', 'conductor_use_id')
            ->whereIn('viaje_transporte.estado_viaje_vjt', ['programado', 'en_curso'])
            ->latest('viaje_transporte.id_vjt');
    }

    public static function columnasDesdeFormulario(array $datos): array
    {
        $columnas = [];

        foreach (self::COLUMNAS_FORMULARIO as $campo => $columna) {
            if (array_key_exists($campo, $datos)) {
                $columnas[$columna] = $datos[$campo];
            }
        }

        if (array_key_exists('activo', $datos)) {
            $columnas['status_id'] = (int) $datos['activo'] === 1 ? self::STATUS_ACTIVO : self::STATUS_INACTIVO;
        }

        return $columnas;
    }

    public static function listarVehiculos($buscar = null, $estado = "todos", $activo = 1)
    {
        return self::query()
            ->with(['modelo.busMarca', 'tipoCombustible', 'sede'])
            ->when($buscar, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('placa_vtr', 'like', "%{$buscar}%")
                ->orWhere('color_vtr', 'like', "%{$buscar}%")))
            ->when($activo !== null && $activo !== '', fn ($q) => $q->where('status_id', (int) $activo === 1 ? self::STATUS_ACTIVO : self::STATUS_INACTIVO))
            ->when($estado !== null && $estado !== '' && $estado !== 'todos', fn ($q) => $q->where('estado_operativo_vtr', $estado))
            ->orderBy('placa_vtr')
            ->paginate(10)
            ->withQueryString();
    }

    public static function crearVehiculo(array $datos)
    {
        return self::create(self::columnasDesdeFormulario($datos));
    }

    public static function actualizarVehiculo(BusVehiculo $vehiculo, array $datos)
    {
        $vehiculo->update(self::columnasDesdeFormulario($datos));

        return $vehiculo;
    }

    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado_operativo_vtr) {
            'disponible'    => '<span class="rd-badge rd-badge-success">Disponible</span>',
            'en_ruta'       => '<span class="rd-badge rd-badge-info">En Ruta</span>',
            'mantenimiento' => '<span class="rd-badge rd-badge-warning">Mantenimiento</span>',
            'inactivo'      => '<span class="rd-badge rd-badge-danger">Inactivo</span>',
            default         => '<span class="rd-badge">Desconocido</span>',
        };
    }
}
