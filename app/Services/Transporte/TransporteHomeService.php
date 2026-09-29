<?php

namespace App\Services\Transporte;

use App\Models\BusCargaCombustible;
use App\Models\BusMantenimiento;
use App\Models\BusRuta;
use App\Models\BusVehiculo;
use App\Models\BusViaje;
use App\Models\Usuario;
use Carbon\Carbon;

class TransporteHomeService
{
    /**
     * Devuelve todos los datos iniciales del panel estadístico de Transporte.
     */
    public function getDashboardData(array $filtros = []): array
    {
        $fechaInicio = $filtros['start_date'] ?? Carbon::now()->subDays(30)->toDateString();
        $fechaFin    = $filtros['end_date']   ?? Carbon::now()->toDateString();

        $viajes  = $this->consultar($filtros, $fechaInicio, $fechaFin);
        $resumen = $this->calcularResumen($viajes, $fechaInicio, $fechaFin);

        return [
            'fechaInicio' => $fechaInicio,
            'fechaFin'    => $fechaFin,
            'vehiculoId'  => $filtros['vehiculo_id'] ?? null,
            'viajes'      => $viajes,
            'resumen'     => $resumen,
            'vehiculos'   => BusVehiculo::where('activo', 1)->orderBy('placa')->get(['id', 'placa']),
            'rutas'       => BusRuta::where('estado', 1)->orderBy('nombre')->get(['id', 'nombre']),
            'conductores' => $this->conductoresElegibles(),
        ];
    }

    /**
     * Query armada con filtros dinámicos.
     */
    protected function consultar(array $f, string $inicio, string $fin)
    {
        $query = BusViaje::with([
            'vehiculo.modelo',
            'ruta',
            'conductor.persona',
            'cargasCombustible',
        ])
            ->whereBetween('fecha_inicio', [$inicio . ' 00:00:00', $fin . ' 23:59:59']);

        if (!empty($f['vehiculo_id'])) {
            $query->where('vehiculo_id', $f['vehiculo_id']);
        }
        if (!empty($f['ruta_id'])) {
            $query->where('bus_ruta_id', $f['ruta_id']);
        }
        if (!empty($f['conductor_id'])) {
            $query->where('conductor_id', $f['conductor_id']);
        }
        if (!empty($f['turno'])) {
            $query->where('turno', $f['turno']);
        }
        if (!empty($f['estado'])) {
            $query->where('estado', $f['estado']);
        }

        return $query->orderBy('fecha_inicio', 'desc')->get();
    }

    /**
     * Conductores disponibles para el filtro.
     */
    protected function conductoresElegibles()
    {
        return Usuario::whereHas('roles', function ($q) {
            $q->whereIn('nombre', ['conductor', 'chofer']);
        })->get()->map(function ($u) {
            $u->nombre_completo = trim(
                ($u->persona->nombre_persona ?? '') . ' ' . ($u->persona->apellido_persona ?? '')
            ) ?: ('Usuario #' . $u->id_usuario);
            return $u;
        });
    }

    /**
     * Toda la lógica de cálculo del resumen.
     */
    protected function calcularResumen($viajes, $fechaInicio, $fechaFin): array
    {
        $resumen = [
            'total_viajes'              => $viajes->count(),
            'total_pasajeros'           => 0,
            'total_km'                  => 0,
            'total_litros'              => 0,
            'total_costo_combustible'   => 0,
            'total_costo_mantenimiento' => 0,
            'km_promedio_viaje'         => 0,
            'pasajeros_promedio'        => 0,
            'eficiencia_km_l'           => 0,
            'costo_por_km'              => 0,
            'costo_por_pasajero'        => 0,
            'viajes_con_desvio'         => 0,
            'por_estado' => [
                'finalizado' => 0,
                'en_curso'   => 0,
                'programado' => 0,
                'cancelado'  => 0,
            ],
            'por_turno' => [
                'mañana' => 0,
                'tarde'  => 0,
                'noche'  => 0,
            ],
            'mantenimientos' => [
                'pendiente'  => 0,
                'en_proceso' => 0,
                'completado' => 0,
            ],
            'distribucion_horas'    => [],
            'flujo_semanal'         => [],
            'pasajeros_por_dia'     => [],
            'rutas'                 => [],
            'vehiculos'             => [],
            'conductores'           => [],
        ];

        $horasBloques    = [];
        $flujoSemanal    = [];
        $pasajerosPorDia = [];
        $rutasSet        = [];
        $vehiculosSet    = [];
        $conductoresSet  = [];

        foreach ($viajes as $v) {
            // Estado y turno
            if (isset($resumen['por_estado'][$v->estado])) {
                $resumen['por_estado'][$v->estado]++;
            }
            if (isset($resumen['por_turno'][$v->turno])) {
                $resumen['por_turno'][$v->turno]++;
            }

            // Totales
            $resumen['total_km']        += (float) $v->distancia_km;
            $resumen['total_litros']    += (float) $v->litros_gastados;
            $resumen['total_pasajeros'] += (int) $v->pasajeros;

            if ($v->hubo_desvio) {
                $resumen['viajes_con_desvio']++;
            }

            // Costo de combustible de este viaje
            if ($v->relationLoaded('cargasCombustible')) {
                $resumen['total_costo_combustible'] += (float) $v->cargasCombustible->sum('total');
            }

            // Agrupaciones temporales
            if ($v->fecha_inicio) {
                $fecha = Carbon::parse($v->fecha_inicio);

                $semanaKey = $fecha->format('W-Y');
                $flujoSemanal[$semanaKey] = ($flujoSemanal[$semanaKey] ?? 0) + 1;

                $bloque = $fecha->format('h:00 A');
                $horasBloques[$bloque] = ($horasBloques[$bloque] ?? 0) + 1;

                $diaKey = $fecha->format('Y-m-d');
                $pasajerosPorDia[$diaKey] = ($pasajerosPorDia[$diaKey] ?? 0) + (int) $v->pasajeros;
            }

            // Agrupaciones por ruta / vehículo / conductor
            $nombreRuta = $v->ruta->nombre ?? 'Sin ruta';
            $rutasSet[$nombreRuta] = ($rutasSet[$nombreRuta] ?? 0) + (int) $v->pasajeros;

            $placa = $v->vehiculo->placa ?? 'Sin placa';
            $vehiculosSet[$placa] = ($vehiculosSet[$placa] ?? 0) + (float) $v->distancia_km;

            if ($v->conductor && $v->conductor->persona) {
                $nombreCond = trim(
                    ($v->conductor->persona->nombre_persona ?? '') . ' ' .
                    ($v->conductor->persona->apellido_persona ?? '')
                ) ?: ('Conductor #' . $v->conductor_id);
                $conductoresSet[$nombreCond] = ($conductoresSet[$nombreCond] ?? 0) + 1;
            }
        }

        // Promedios y ratios
        if ($resumen['total_viajes'] > 0) {
            $resumen['km_promedio_viaje']  = round($resumen['total_km'] / $resumen['total_viajes'], 2);
            $resumen['pasajeros_promedio'] = round($resumen['total_pasajeros'] / $resumen['total_viajes'], 2);
        }
        if ($resumen['total_litros'] > 0) {
            $resumen['eficiencia_km_l'] = round($resumen['total_km'] / $resumen['total_litros'], 2);
        }
        if ($resumen['total_km'] > 0) {
            $resumen['costo_por_km'] = round($resumen['total_costo_combustible'] / $resumen['total_km'], 2);
        }
        if ($resumen['total_pasajeros'] > 0) {
            $resumen['costo_por_pasajero'] = round(
                $resumen['total_costo_combustible'] / $resumen['total_pasajeros'], 2
            );
        }

        // Hora pico
        arsort($horasBloques);
        $resumen['hora_pico'] = !empty($horasBloques) ? array_key_first($horasBloques) : 'N/A';
        $resumen['distribucion_horas'] = $horasBloques;

        // Flujo semanal ordenado
        ksort($flujoSemanal);
        $resumen['flujo_semanal'] = $flujoSemanal;

        // Pasajeros por día ordenado
        ksort($pasajerosPorDia);
        $resumen['pasajeros_por_dia'] = $pasajerosPorDia;

        // Agrupaciones ordenadas
        arsort($rutasSet);
        arsort($vehiculosSet);
        arsort($conductoresSet);
        $resumen['rutas']       = $rutasSet;
        $resumen['vehiculos']   = $vehiculosSet;
        $resumen['conductores'] = $conductoresSet;

        // Mantenimientos (fuera del rango de viajes)
        $resumen['mantenimientos']['pendiente']  = BusMantenimiento::where('estado', 'pendiente')->count();
        $resumen['mantenimientos']['en_proceso'] = BusMantenimiento::where('estado', 'en_proceso')->count();
        $resumen['mantenimientos']['completado'] = BusMantenimiento::where('estado', 'completado')->count();
        $resumen['total_costo_mantenimiento']    = (float) BusMantenimiento::sum('costo');

        // Promedio semanal
        $semanas = 1;
        if ($fechaInicio && $fechaFin) {
            $semanas = max(1, Carbon::parse($fechaInicio)->diffInDays(Carbon::parse($fechaFin))) / 7;
        }
        $resumen['promedio_semanal'] = round($resumen['total_viajes'] / max(0.1, $semanas), 1);

        // Comparativa vs período anterior
        $resumen['comparativa_viajes'] = 0;
        if ($fechaInicio && $fechaFin) {
            $inicio     = Carbon::parse($fechaInicio);
            $fin        = Carbon::parse($fechaFin);
            $dias       = $inicio->diffInDays($fin);
            $prevFin    = $inicio->copy()->subDay();
            $prevInicio = $prevFin->copy()->subDays($dias);

            $prev = BusViaje::whereBetween('fecha_inicio', [
                $prevInicio->toDateString() . ' 00:00:00',
                $prevFin->toDateString() . ' 23:59:59',
            ])->count();

            if ($prev > 0) {
                $resumen['comparativa_viajes'] = round(
                    (($resumen['total_viajes'] - $prev) / $prev) * 100, 1
                );
            } elseif ($resumen['total_viajes'] > 0) {
                $resumen['comparativa_viajes'] = 100;
            }
        }

        return $resumen;
    }
}