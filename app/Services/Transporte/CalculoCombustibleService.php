<?php

namespace App\Services\Transporte;

use App\Models\BusViaje;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CalculoCombustibleService
{
    private const RESULTADO_VACIO = [
        'distancia_km'        => 0.0,
        'distancia_urbana_km' => 0.0,
        'distancia_carretera_km' => 0.0,
        'tiempo_ralenti_horas'   => 0.0,
        'litros_gastados'        => 0.0,
        'factor_carga'           => 1.0,
        'fuente'                 => 'gps',
        'puntos_gps_usados'      => 0,
    ];

    public function __construct(private TrazadoRutaService $trazado)
    {
    }

    public function calcularParaViaje(BusViaje $viaje): array
    {
        $vehiculo = $viaje->vehiculo;

        if (!$vehiculo) {
            throw new \RuntimeException('El viaje no tiene un vehículo asociado; no se puede calcular combustible.');
        }

        $puntos = $viaje->gpsLogs()
            ->orderBy('registrado_en_lgt')
            ->orderBy('id_lgt')
            ->get(['lat_lgt', 'lng_lgt', 'velocidad_lgt', 'registrado_en_lgt']);

        $puntosRecibidos = (int) $viaje->puntos_recibidos_vjt;

        if ($puntosRecibidos > $puntos->count()) {
            $distanciaUrbana = (float) $viaje->distancia_urbana_km_vjt;
            $distanciaCarretera = (float) $viaje->distancia_carretera_km_vjt;
            $tiempoRalentiHoras = (float) $viaje->ralenti_horas_vjt;
            $puntosUsados = $puntosRecibidos;

            if ($distanciaUrbana + $distanciaCarretera + $tiempoRalentiHoras <= 0) {
                return $this->calcularConRutaPlanificada($viaje, $vehiculo);
            }
        } elseif ($puntos->count() < 2) {
            return $this->calcularConRutaPlanificada($viaje, $vehiculo);
        } else {
            [$distanciaUrbana, $distanciaCarretera, $tiempoRalentiHoras] = $this->analizarTramos($puntos);
            $puntosUsados = $puntos->count();
        }

        $distanciaTotal = $distanciaUrbana + $distanciaCarretera;

        $litrosPorDistancia = ($distanciaUrbana * (float) $vehiculo->consumo_urbano_vtr)
            + ($distanciaCarretera * (float) $vehiculo->consumo_carretera_vtr);

        $litrosPorRalenti = $tiempoRalentiHoras * (float) $vehiculo->consumo_ralenti_vtr;

        $factorCarga = $this->calcularFactorCarga($vehiculo, (int) $viaje->total_pasajeros_vjt);

        $litrosGastados = max(0, ($litrosPorDistancia + $litrosPorRalenti) * $factorCarga);

        return [
            'distancia_km'           => round($distanciaTotal, 2),
            'distancia_urbana_km'    => round($distanciaUrbana, 2),
            'distancia_carretera_km' => round($distanciaCarretera, 2),
            'tiempo_ralenti_horas'   => round($tiempoRalentiHoras, 4),
            'litros_gastados'        => round($litrosGastados, 2),
            'factor_carga'           => round($factorCarga, 4),
            'fuente'                 => 'gps',
            'puntos_gps_usados'      => $puntosUsados,
        ];
    }

    private function calcularConRutaPlanificada(BusViaje $viaje, $vehiculo): array
    {
        $distanciaReferencia = $this->trazado->longitudReferenciaKm((int) $viaje->rtr_id);
        $distanciaPlanificada = $distanciaReferencia > 0
            ? $distanciaReferencia
            : (float) ($viaje->ruta->distancia_km_rtr ?? 0);

        $factorCarga = $this->calcularFactorCarga($vehiculo, (int) $viaje->total_pasajeros_vjt);

        $litrosGastados = max(0, $distanciaPlanificada * (float) $vehiculo->consumo_carretera_vtr * $factorCarga);

        return [
            'distancia_km'           => round($distanciaPlanificada, 2),
            'distancia_urbana_km'    => 0.0,
            'distancia_carretera_km' => round($distanciaPlanificada, 2),
            'tiempo_ralenti_horas'   => 0.0,
            'litros_gastados'        => round($litrosGastados, 2),
            'factor_carga'           => round($factorCarga, 4),
            'fuente'                 => 'ruta_planificada',
            'puntos_gps_usados'      => 0,
        ];
    }

    private function analizarTramos(Collection $puntos): array
    {
        $umbralCarretera = (float) config('transporte.umbral_velocidad_carretera_kmh', 45);
        $umbralRalenti   = (float) config('transporte.umbral_velocidad_ralenti_kmh', 3);
        $topeGapHoras    = (float) config('transporte.ralenti_max_gap_horas', 0.5);

        $distanciaUrbana    = 0.0;
        $distanciaCarretera = 0.0;
        $ralentiHoras       = 0.0;

        $anterior = null;

        foreach ($puntos as $punto) {
            if ($anterior === null) {
                $anterior = $punto;
                continue;
            }

            $velAnterior = (float) ($anterior->velocidad_lgt ?? 0);
            $velActual   = (float) ($punto->velocidad_lgt ?? 0);

            $esRalenti = $velAnterior <= $umbralRalenti && $velActual <= $umbralRalenti;

            if ($esRalenti) {
                $horas = $this->horasEntre($anterior->registrado_en_lgt, $punto->registrado_en_lgt);
                $ralentiHoras += min($horas, $topeGapHoras);
            } else {
                $tramoKm = $this->distanciaHaversineKm(
                    (float) $anterior->lat_lgt,
                    (float) $anterior->lng_lgt,
                    (float) $punto->lat_lgt,
                    (float) $punto->lng_lgt
                );

                $velocidadPromedioTramo = ($velAnterior + $velActual) / 2;

                if ($velocidadPromedioTramo >= $umbralCarretera) {
                    $distanciaCarretera += $tramoKm;
                } else {
                    $distanciaUrbana += $tramoKm;
                }
            }

            $anterior = $punto;
        }

        return [$distanciaUrbana, $distanciaCarretera, $ralentiHoras];
    }

    private function horasEntre($desde, $hasta): float
    {
        if (!$desde || !$hasta) {
            return 0.0;
        }

        $desde = $desde instanceof Carbon ? $desde : Carbon::parse($desde);
        $hasta = $hasta instanceof Carbon ? $hasta : Carbon::parse($hasta);

        $segundos = max(0, $hasta->diffInSeconds($desde));

        return $segundos / 3600;
    }

    private function distanciaHaversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $radioTierraKm = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $radioTierraKm * $c;
    }

    private function calcularFactorCarga($vehiculo, int $pasajeros): float
    {
        $pesoVehiculo = (float) $vehiculo->peso_vtr;

        if ($pesoVehiculo <= 0) {
            return 1.0;
        }

        $pesoEstudianteKg = (float) config('transporte.peso_estudiante_kg', 60);
        $multiplicador    = (float) config('transporte.factor_peso_multiplicador', 0.3);

        $pesoExtraFraccion = ($pasajeros * $pesoEstudianteKg) / $pesoVehiculo;

        return 1 + ($pesoExtraFraccion * $multiplicador);
    }

    public function aplicarYGuardar(BusViaje $viaje, array $resultado): void
    {
        $vehiculo = $viaje->vehiculo;

        $nivelResultante = (float) $vehiculo->nivel_combustible_actual_vtr - $resultado['litros_gastados'];
        $nivelResultante = max(0, min($nivelResultante, (float) $vehiculo->capacidad_tanque_litros_vtr));

        $vehiculo->update([
            'nivel_combustible_actual_vtr' => $nivelResultante,
            'km_actual_vtr'                => (float) $vehiculo->km_actual_vtr + $resultado['distancia_km'],
        ]);

        $viaje->update([
            'km_fin_vjt'          => (float) $viaje->km_inicio_vjt + $resultado['distancia_km'],
            'distancia_km_vjt'    => $resultado['distancia_km'],
            'litros_gastados_vjt' => $resultado['litros_gastados'],
        ]);
    }
}