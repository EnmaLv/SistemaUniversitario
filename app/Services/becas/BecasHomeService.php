<?php

namespace App\Services\becas;

use App\Models\Becas\BecaAsignada;
use App\Models\Becas\Beneficio;
use App\Models\Becas\JornadaBeca;
use App\Models\Becas\Lapso;
use App\Models\Becas\SolicitudBeca;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BecasHomeService
{
    /**
     * Devuelve todos los datos iniciales del panel estadístico de Becas.
     */
    public function getDashboardData(array $filtros = []): array
    {
        $fechaInicio = $filtros['start_date'] ?? Carbon::now()->subDays(30)->toDateString();
        $fechaFin    = $filtros['end_date']   ?? Carbon::now()->toDateString();

        $solicitudes = $this->consultarSolicitudes($filtros, $fechaInicio, $fechaFin);
        $resumen     = $this->calcularResumen($solicitudes, $fechaInicio, $fechaFin);

        return [
            'fechaInicio' => $fechaInicio,
            'fechaFin'    => $fechaFin,
            'solicitudes' => $solicitudes,
            'resumen'     => $resumen,
            'beneficios'  => Beneficio::orderBy('nombre_beneficio')->get(['id', 'nombre_beneficio']),
            'jornadas'    => JornadaBeca::orderBy('nombre_jornada')->get(['id', 'nombre_jornada']),
            'lapsos'      => Lapso::orderBy('codigo')->get(['id', 'codigo']),
        ];
    }

    /**
     * Query de solicitudes con filtros dinámicos.
     */
    protected function consultarSolicitudes(array $f, string $inicio, string $fin)
    {
        $query = SolicitudBeca::with([
            'persona',
            'beneficio',
            'jornada',
            'lapso',
            'verificador.persona',
        ])
            ->whereBetween('created_at', [$inicio . ' 00:00:00', $fin . ' 23:59:59']);

        if (!empty($f['beneficio_id'])) {
            $query->where('id_beneficio', $f['beneficio_id']);
        }
        if (!empty($f['jornada_id'])) {
            $query->where('jornada_id', $f['jornada_id']);
        }
        if (isset($f['estado']) && $f['estado'] !== '' && $f['estado'] !== null) {
            $query->where('estado', (int) $f['estado']);
        }
        if (!empty($f['tipo_solicitud'])) {
            $query->where('tipo_solicitud', $f['tipo_solicitud']);
        }
        if (!empty($f['lapso_id'])) {
            $query->where('id_lapso', $f['lapso_id']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Toda la lógica de cálculo del resumen.
     */
    protected function calcularResumen($solicitudes, $fechaInicio, $fechaFin): array
    {
        $resumen = [
            'total_solicitudes'       => $solicitudes->count(),
            'pendientes'              => 0,
            'aprobadas'               => 0,
            'rechazadas'              => 0,
            'tasa_aprobacion'         => 0,
            'tasa_rechazo'            => 0,
            'jornadas_activas'        => JornadaBeca::where('activa', 1)->count(),
            'beneficios_activos'      => Beneficio::where('status', 1)->count(),
            'cupones_disponibles'     => (int) Beneficio::sum('cupones_disponibles'),
            'cupones_ocupados'        => (int) Beneficio::sum('cupones_ocupados'),
            'becas_asignadas_activas' => BecaAsignada::where('estado', 1)->count(),
            'tiempo_promedio_verif'   => 0,
            'solicitudes_nuevas'      => 0,
            'solicitudes_renovacion'  => 0,
            'por_estado'              => ['pendientes' => 0, 'aprobadas' => 0, 'rechazadas' => 0],
            'por_tipo'                => [],
            'por_beneficio'           => [],
            'por_jornada'             => [],
            'por_lapso'               => [],
            'por_dia'                 => [],
            'flujo_semanal'           => [],
            'horas'                   => [],
            'top_verificadores'       => [],
            'cupones_por_beneficio'   => [],
        ];

        $diasVerificacion = [];
        $porBeneficio     = [];
        $porJornada       = [];
        $porLapso         = [];
        $porTipo          = [];
        $porDia           = [];
        $porSemana        = [];
        $porHora          = [];
        $verificadores    = [];

        foreach ($solicitudes as $s) {
            // Estados
            if ($s->estado === 0) {
                $resumen['pendientes']++;
                $resumen['por_estado']['pendientes']++;
            } elseif ($s->estado === 1) {
                $resumen['aprobadas']++;
                $resumen['por_estado']['aprobadas']++;
            } elseif ($s->estado === 2) {
                $resumen['rechazadas']++;
                $resumen['por_estado']['rechazadas']++;
            }

            // Tipo de solicitud
            $tipo = $s->tipo_solicitud ?: 'No especificado';
            $porTipo[$tipo] = ($porTipo[$tipo] ?? 0) + 1;

            // Beneficio
            $nombreBeneficio = $s->beneficio->nombre_beneficio ?? 'Sin beneficio';
            $porBeneficio[$nombreBeneficio] = ($porBeneficio[$nombreBeneficio] ?? 0) + 1;

            // Jornada
            $nombreJornada = $s->jornada->nombre_jornada ?? 'Sin jornada';
            $porJornada[$nombreJornada] = ($porJornada[$nombreJornada] ?? 0) + 1;

            // Lapso
            $codigoLapso = $s->lapso->codigo ?? 'Sin lapso';
            $porLapso[$codigoLapso] = ($porLapso[$codigoLapso] ?? 0) + 1;

            // Fecha de creación
            if ($s->created_at) {
                $fecha = Carbon::parse($s->created_at);

                $diaKey = $fecha->format('Y-m-d');
                $porDia[$diaKey] = ($porDia[$diaKey] ?? 0) + 1;

                $semanaKey = $fecha->format('W-Y');
                $porSemana[$semanaKey] = ($porSemana[$semanaKey] ?? 0) + 1;

                $bloqueHora = $fecha->format('h:00 A');
                $porHora[$bloqueHora] = ($porHora[$bloqueHora] ?? 0) + 1;
            }

            // Tiempo promedio de verificación
            if ($s->fecha_verificacion && $s->created_at) {
                $creada = Carbon::parse($s->created_at);
                $verificada = Carbon::parse($s->fecha_verificacion);
                $diasVerificacion[] = $creada->diffInHours($verificada);
            }

            // Verificador
            if ($s->verificado_por && $s->verificador) {
                $persona = $s->verificador->persona;
                $nombre = trim(
                    ($persona->nombre_persona ?? '') . ' ' . ($persona->apellido_persona ?? '')
                ) ?: ('Verificador #' . $s->verificado_por);
                $verificadores[$nombre] = ($verificadores[$nombre] ?? 0) + 1;
            }
        }

        // Tipos detectados dinámicamente
        if (isset($porTipo['nueva']) || isset($porTipo['Nueva'])) {
            $resumen['solicitudes_nuevas'] = ($porTipo['nueva'] ?? 0) + ($porTipo['Nueva'] ?? 0);
        }
        if (isset($porTipo['renovacion']) || isset($porTipo['Renovación']) || isset($porTipo['renovación'])) {
            $resumen['solicitudes_renovacion'] =
                ($porTipo['renovacion'] ?? 0) +
                ($porTipo['Renovación'] ?? 0) +
                ($porTipo['renovación'] ?? 0);
        }

        // Tasas
        if ($resumen['total_solicitudes'] > 0) {
            $resumen['tasa_aprobacion'] = round(
                ($resumen['aprobadas'] / $resumen['total_solicitudes']) * 100, 1
            );
            $resumen['tasa_rechazo'] = round(
                ($resumen['rechazadas'] / $resumen['total_solicitudes']) * 100, 1
            );
        }

        // Tiempo promedio de verificación (horas)
        if (count($diasVerificacion) > 0) {
            $resumen['tiempo_promedio_verif'] = round(array_sum($diasVerificacion) / count($diasVerificacion), 1);
        }

        // Cupones por beneficio
        $resumen['cupones_por_beneficio'] = Beneficio::where('status', 1)
            ->orderBy('nombre_beneficio')
            ->get(['nombre_beneficio', 'cupones_disponibles', 'cupones_ocupados'])
            ->mapWithKeys(fn ($b) => [
                $b->nombre_beneficio => [
                    'disponibles' => (int) $b->cupones_disponibles,
                    'ocupados'    => (int) $b->cupones_ocupados,
                ]
            ])
            ->toArray();

        // Ordenar
        arsort($porBeneficio);
        arsort($porJornada);
        arsort($porLapso);
        arsort($porTipo);
        arsort($porHora);
        arsort($verificadores);
        ksort($porDia);
        ksort($porSemana);

        $resumen['por_beneficio']     = $porBeneficio;
        $resumen['por_jornada']       = $porJornada;
        $resumen['por_lapso']         = $porLapso;
        $resumen['por_tipo']          = $porTipo;
        $resumen['por_dia']           = $porDia;
        $resumen['flujo_semanal']     = $porSemana;
        $resumen['horas']             = $porHora;
        $resumen['top_verificadores'] = $verificadores;

        return $resumen;
    }
}