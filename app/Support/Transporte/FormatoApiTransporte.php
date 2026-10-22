<?php

namespace App\Support\Transporte;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class FormatoApiTransporte
{
    public static function marca(Model $marca): array
    {
        return [
            'id'          => self::entero(self::crudo($marca, 'id_mav')),
            'nombre'      => self::crudo($marca, 'nombre_mav'),
            'descripcion' => self::crudo($marca, 'descripcion_mav') ?? 'Ninguna',
            'estado'      => self::activo($marca),
            'created_at'  => self::fecha($marca->created_at),
            'updated_at'  => self::fecha($marca->updated_at),
        ];
    }

    public static function modelo(Model $modelo): array
    {
        $datos = [
            'id'          => self::entero(self::crudo($modelo, 'id_mov')),
            'marca_id'    => self::entero(self::crudo($modelo, 'mav_id')),
            'nombre'      => self::crudo($modelo, 'nombre_mov'),
            'descripcion' => self::crudo($modelo, 'descripcion_mov') ?? 'Ninguna',
            'estado'      => self::activo($modelo),
            'created_at'  => self::fecha($modelo->created_at),
            'updated_at'  => self::fecha($modelo->updated_at),
        ];

        if ($modelo->relationLoaded('busMarca')) {
            $datos['bus_marca'] = $modelo->busMarca ? self::marca($modelo->busMarca) : null;
        }

        return $datos;
    }

    public static function tipoCombustible(Model $tipo): array
    {
        return [
            'id'          => self::entero(self::crudo($tipo, 'id_tcb')),
            'nombre'      => self::crudo($tipo, 'nombre_tcb'),
            'descripcion' => self::crudo($tipo, 'descripcion_tcb'),
            'estado'      => self::activo($tipo),
            'created_at'  => self::fecha($tipo->created_at),
            'updated_at'  => self::fecha($tipo->updated_at),
        ];
    }

    public static function paradaBasica(Model $parada): array
    {
        return [
            'id'        => self::entero(self::crudo($parada, 'id_ptr')),
            'nombre'    => self::crudo($parada, 'nombre_ptr'),
            'lat'       => self::crudo($parada, 'lat_ptr'),
            'lng'       => self::crudo($parada, 'lng_ptr'),
            'direccion' => self::crudo($parada, 'direccion_ptr') ?? 'Ninguna',
        ];
    }

    public static function parada(Model $parada): array
    {
        $datos = self::paradaBasica($parada) + [
            'estado'     => self::activo($parada),
            'created_at' => self::fecha($parada->created_at),
            'updated_at' => self::fecha($parada->updated_at),
        ];

        if ($parada->relationLoaded('pivot') && $parada->pivot) {
            $datos['pivot'] = [
                'bus_ruta_id'   => self::entero($parada->pivot->rtr_id),
                'bus_parada_id' => self::entero($parada->pivot->ptr_id),
                'orden'         => self::entero($parada->pivot->orden_rpt),
            ];
        }

        return $datos;
    }

    public static function ruta(Model $ruta): array
    {
        $datos = [
            'id'           => self::entero(self::crudo($ruta, 'id_rtr')),
            'nombre'       => self::crudo($ruta, 'nombre_rtr'),
            'distancia_km' => self::crudo($ruta, 'distancia_km_rtr'),
            'descripcion'  => self::crudo($ruta, 'descripcion_rtr') ?? 'Ninguna',
            'sed_id'       => self::entero(self::crudo($ruta, 'sed_id')),
            'estado'       => self::activo($ruta),
            'created_at'   => self::fecha($ruta->created_at),
            'updated_at'   => self::fecha($ruta->updated_at),
        ];

        if ($ruta->relationLoaded('paradas')) {
            $datos['paradas'] = $ruta->paradas->map(fn ($parada) => self::parada($parada))->values()->all();
        }

        return $datos;
    }

    public static function vehiculo(Model $vehiculo, bool $incluirConductor = false): array
    {
        $datos = [
            'id'                       => self::entero(self::crudo($vehiculo, 'id_vtr')),
            'placa'                    => self::crudo($vehiculo, 'placa_vtr'),
            'modelo_id'                => self::entero(self::crudo($vehiculo, 'mov_id')),
            'anio'                     => self::entero(self::crudo($vehiculo, 'anio_vtr')),
            'color'                    => self::crudo($vehiculo, 'color_vtr'),
            'peso'                     => self::crudo($vehiculo, 'peso_vtr'),
            'cantidad_pasajeros'       => self::entero(self::crudo($vehiculo, 'capacidad_pasajeros_vtr')),
            'tipo_combustible_id'      => self::entero(self::crudo($vehiculo, 'tcb_id')),
            'cantidad_cilindros'       => self::entero(self::crudo($vehiculo, 'cantidad_cilindros_vtr')),
            'capacidad_tanque_litros'  => self::decimal(self::crudo($vehiculo, 'capacidad_tanque_litros_vtr'), 2),
            'nivel_combustible_actual' => self::crudo($vehiculo, 'nivel_combustible_actual_vtr'),
            'consumo_urbano'           => self::decimal(self::crudo($vehiculo, 'consumo_urbano_vtr'), 3),
            'consumo_carretera'        => self::decimal(self::crudo($vehiculo, 'consumo_carretera_vtr'), 3),
            'consumo_relenti'          => self::decimal(self::crudo($vehiculo, 'consumo_ralenti_vtr'), 3),
            'km_actual'                => self::decimal(self::crudo($vehiculo, 'km_actual_vtr'), 2),
            'km_proximo_mantenimiento' => self::decimal(self::crudo($vehiculo, 'km_proximo_mantenimiento_vtr'), 2),
            'sed_id'                   => self::entero(self::crudo($vehiculo, 'sed_id')),
            'activo'                   => self::activo($vehiculo),
            'estado'                   => self::crudo($vehiculo, 'estado_operativo_vtr'),
            'created_at'               => self::fecha($vehiculo->created_at),
            'updated_at'               => self::fecha($vehiculo->updated_at),
        ];

        if ($vehiculo->relationLoaded('modelo')) {
            $datos['modelo'] = $vehiculo->modelo ? self::modelo($vehiculo->modelo) : null;
        }

        if ($vehiculo->relationLoaded('tipoCombustible')) {
            $datos['tipo_combustible'] = $vehiculo->tipoCombustible ? self::tipoCombustible($vehiculo->tipoCombustible) : null;
        }

        if ($vehiculo->relationLoaded('sede')) {
            $datos['sede'] = $vehiculo->sede?->toArray();
        }

        if ($incluirConductor) {
            $datos['conductor'] = null;
        }

        return $datos;
    }

    public static function viaje(Model $viaje): array
    {
        $datos = [
            'id'              => self::entero(self::crudo($viaje, 'id_vjt')),
            'vehiculo_id'     => self::entero(self::crudo($viaje, 'vtr_id')),
            'bus_ruta_id'     => self::entero(self::crudo($viaje, 'rtr_id')),
            'conductor_id'    => self::entero(self::crudo($viaje, 'conductor_use_id')),
            'turno'           => self::crudo($viaje, 'turno_vjt'),
            'firebase_id'     => self::crudo($viaje, 'firebase_id_vjt'),
            'fecha_inicio'    => self::fecha(self::crudo($viaje, 'fecha_inicio_vjt')),
            'km_inicio'       => self::decimal(self::crudo($viaje, 'km_inicio_vjt'), 2),
            'km_fin'          => self::decimal(self::crudo($viaje, 'km_fin_vjt'), 2),
            'distancia_km'    => self::decimal(self::crudo($viaje, 'distancia_km_vjt'), 2),
            'litros_gastados' => self::decimal(self::crudo($viaje, 'litros_gastados_vjt'), 2),
            'pasajeros'       => self::entero(self::crudo($viaje, 'total_pasajeros_vjt')),
            'hubo_desvio'     => self::booleano(self::crudo($viaje, 'hubo_desvio_vjt')),
            'motivo_desvio'   => self::crudo($viaje, 'motivo_desvio_vjt'),
            'estado'          => self::crudo($viaje, 'estado_viaje_vjt'),
            'created_at'      => self::fecha($viaje->created_at),
            'updated_at'      => self::fecha($viaje->updated_at),
        ];

        if ($viaje->relationLoaded('vehiculo')) {
            $datos['vehiculo'] = $viaje->vehiculo ? self::vehiculo($viaje->vehiculo) : null;
        }

        if ($viaje->relationLoaded('busRuta')) {
            $datos['bus_ruta'] = $viaje->busRuta ? self::ruta($viaje->busRuta) : null;
        }

        if ($viaje->relationLoaded('conductor')) {
            $datos['conductor'] = $viaje->conductor?->toArray();
        }

        return $datos;
    }

    public static function decimal($valor, int $escala): ?string
    {
        return $valor === null ? null : number_format((float) $valor, $escala, '.', '');
    }

    public static function fecha($valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        return ($valor instanceof DateTimeInterface ? Carbon::instance($valor) : Carbon::parse($valor))->toJSON();
    }

    private static function crudo(Model $modelo, string $columna)
    {
        return $modelo->getAttributes()[$columna] ?? null;
    }

    private static function entero($valor): ?int
    {
        return $valor === null ? null : (int) $valor;
    }

    private static function booleano($valor): ?bool
    {
        return $valor === null ? null : (bool) $valor;
    }

    private static function activo(Model $modelo): bool
    {
        return (int) self::crudo($modelo, 'status_id') === 1;
    }
}
