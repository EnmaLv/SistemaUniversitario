<?php

namespace App\Services\Transporte;

use App\Models\BusViaje;
use App\Support\Auditoria\Bitacora;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TrazadoRutaService
{
    private const RADIO_TIERRA_M = 6371000.0;

    private const VELOCIDAD_MAXIMA_COLUMNA = 9999.99;

    public function procesarPunto(BusViaje $viaje, array $punto): bool
    {
        return DB::transaction(function () use ($viaje, $punto) {
            $fila = DB::table('viaje_transporte')
                ->where('id_vjt', $viaje->getKey())
                ->lockForUpdate()
                ->first();

            $localId = (string) $punto['local_id'];

            if ($fila->ultimo_local_id_vjt !== null && strcasecmp((string) $fila->ultimo_local_id_vjt, $localId) === 0) {
                return true;
            }

            if (DB::table('log_gps_transporte')->where('local_id_lgt', $localId)->exists()) {
                return true;
            }

            $lat = (float) $punto['lat'];
            $lng = (float) $punto['lng'];
            $registradoEn = Carbon::parse($punto['timestamp']);
            $momento = $registradoEn->format('Y-m-d H:i:s');
            $esPosterior = $fila->ultima_posicion_en_vjt === null
                || strcmp($momento, substr((string) $fila->ultima_posicion_en_vjt, 0, 19)) > 0;

            $trazado = $this->puntosVigentes((int) $fila->rtr_id);
            $cambios = [];

            if ($trazado === []) {
                $this->guardarLog($viaje, $punto, $registradoEn, null);
            } elseif ($this->distanciaATrazadoMetros($lat, $lng, $trazado) <= $this->toleranciaDesvio()) {
                if (! $esPosterior) {
                    return true;
                }

                if ($fila->en_desvio_vjt) {
                    $cambios['en_desvio_vjt'] = false;
                }
            } else {
                $tramos = (int) $fila->tramos_desvio_vjt;

                if ($esPosterior && ! $fila->en_desvio_vjt) {
                    $tramo = $tramos + 1;
                    $cambios['en_desvio_vjt'] = true;
                } else {
                    $tramo = max(1, $tramos);
                }

                $cambios['tramos_desvio_vjt'] = max($tramos, $tramo);
                $cambios['hubo_desvio_vjt'] = true;

                $this->guardarLog($viaje, $punto, $registradoEn, $tramo);
            }

            if ($esPosterior) {
                $cambios += $this->acumulados($fila, $lat, $lng, (float) ($punto['velocidad'] ?? 0), $momento);
            }

            $cambios['ultimo_local_id_vjt'] = $localId;

            DB::table('viaje_transporte')->where('id_vjt', $viaje->getKey())->update($cambios);

            return false;
        });
    }

    public function generarReferenciaSiFalta(BusViaje $viaje): ?int
    {
        $rutaId = (int) $viaje->rtr_id;

        if ($this->versionVigente($rutaId) !== null) {
            return null;
        }

        $puntos = array_map(fn ($log) => [$log[0], $log[1]], $this->logsDelViaje((int) $viaje->getKey()));

        if (count($puntos) < 2) {
            return null;
        }

        $version = $this->guardarVersion($rutaId, (int) $viaje->getKey(), $this->simplificar($puntos), true);

        if ($version !== null) {
            Bitacora::registrar(
                accion: 'trazado_referencia_generado',
                modulo: 'transporte',
                tablaAfectada: 'ruta_transporte',
                registroId: $rutaId,
                datosModificados: ['version_vigente' => $version, 'vjt_id' => (int) $viaje->getKey()],
            );
        }

        return $version;
    }

    public function marcarComoReferencia(BusViaje $viaje): array
    {
        $rutaId = (int) $viaje->rtr_id;
        $logs = $this->logsDelViaje((int) $viaje->getKey());
        $completo = (int) $viaje->puntos_recibidos_vjt <= count($logs);
        $referencia = $this->puntosVigentes($rutaId);

        if ($completo || $referencia === []) {
            $puntos = array_map(fn ($log) => [$log[0], $log[1]], $logs);
        } else {
            $tramos = [];
            foreach ($logs as $log) {
                if ($log[2] !== null) {
                    $tramos[$log[2]][] = [$log[0], $log[1]];
                }
            }

            if ($tramos === []) {
                throw new RuntimeException('El viaje siguió la ruta de referencia vigente; no hay cambios que marcar.');
            }

            ksort($tramos);
            $puntos = $referencia;
            foreach ($tramos as $tramo) {
                $puntos = $this->insertarTramo($puntos, $tramo);
            }
        }

        if (count($puntos) < 2) {
            throw new RuntimeException('El viaje no tiene puntos GPS suficientes para construir un trazado.');
        }

        $anterior = $this->versionVigente($rutaId);
        $simplificado = $this->simplificar($puntos);
        $version = $this->guardarVersion($rutaId, (int) $viaje->getKey(), $simplificado, false);

        return [
            'version_anterior' => $anterior,
            'version'          => $version,
            'puntos'           => count($simplificado),
        ];
    }

    public function versionVigente(int $rutaId): ?int
    {
        $version = DB::table('trazado_ruta_transporte')
            ->where('rtr_id', $rutaId)
            ->where('vigente_trt', true)
            ->whereNull('deleted_at')
            ->max('version_trt');

        return $version === null ? null : (int) $version;
    }

    public function puntosVigentes(int $rutaId): array
    {
        $minutos = (int) config('transporte.cache_trazado_minutos', 10);

        return Cache::remember(self::claveCache($rutaId), now()->addMinutes($minutos), function () use ($rutaId) {
            $version = $this->versionVigente($rutaId);

            if ($version === null) {
                return [];
            }

            return DB::table('trazado_ruta_transporte')
                ->where('rtr_id', $rutaId)
                ->where('version_trt', $version)
                ->whereNull('deleted_at')
                ->orderBy('orden_trt')
                ->get(['lat_trt', 'lng_trt'])
                ->map(fn ($p) => [(float) $p->lat_trt, (float) $p->lng_trt])
                ->all();
        });
    }

    public function longitudReferenciaKm(int $rutaId): float
    {
        return $this->longitudKm($this->puntosVigentes($rutaId));
    }

    public function tramosDesvio(int $viajeId): array
    {
        $tramos = [];

        foreach ($this->logsDelViaje($viajeId) as $log) {
            if ($log[2] !== null) {
                $tramos[$log[2]][] = [$log[0], $log[1]];
            }
        }

        ksort($tramos);

        return array_values($tramos);
    }

    public function distanciaATrazadoMetros(float $lat, float $lng, array $trazado): float
    {
        $total = count($trazado);

        if ($total === 0) {
            return INF;
        }

        if ($total === 1) {
            return $this->distanciaMetros($lat, $lng, $trazado[0][0], $trazado[0][1]);
        }

        $minima = INF;
        for ($i = 0; $i < $total - 1; $i++) {
            $minima = min($minima, $this->distanciaPuntoSegmentoMetros([$lat, $lng], $trazado[$i], $trazado[$i + 1]));
        }

        return $minima;
    }

    public function simplificar(array $puntos, ?float $toleranciaMetros = null): array
    {
        $puntos = array_values($puntos);
        $total = count($puntos);

        if ($total < 3) {
            return $puntos;
        }

        $tolerancia = $toleranciaMetros ?? (float) config('transporte.tolerancia_simplificacion_metros', 10);
        $conservar = array_fill(0, $total, false);
        $conservar[0] = true;
        $conservar[$total - 1] = true;
        $pila = [[0, $total - 1]];

        while ($pila !== []) {
            [$inicio, $fin] = array_pop($pila);
            $maxima = 0.0;
            $indice = null;

            for ($i = $inicio + 1; $i < $fin; $i++) {
                $distancia = $this->distanciaPuntoSegmentoMetros($puntos[$i], $puntos[$inicio], $puntos[$fin]);
                if ($distancia > $maxima) {
                    $maxima = $distancia;
                    $indice = $i;
                }
            }

            if ($indice !== null && $maxima > $tolerancia) {
                $conservar[$indice] = true;
                $pila[] = [$inicio, $indice];
                $pila[] = [$indice, $fin];
            }
        }

        $resultado = [];
        foreach ($puntos as $i => $punto) {
            if ($conservar[$i]) {
                $resultado[] = $punto;
            }
        }

        return $resultado;
    }

    public function longitudKm(array $puntos): float
    {
        $metros = 0.0;

        for ($i = 1, $total = count($puntos); $i < $total; $i++) {
            $metros += $this->distanciaMetros($puntos[$i - 1][0], $puntos[$i - 1][1], $puntos[$i][0], $puntos[$i][1]);
        }

        return $metros / 1000;
    }

    public function distanciaMetros(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return self::RADIO_TIERRA_M * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    public static function claveCache(int $rutaId): string
    {
        return "transporte:trazado_vigente:{$rutaId}";
    }

    private function distanciaPuntoSegmentoMetros(array $punto, array $a, array $b): float
    {
        $coseno = cos(deg2rad($punto[0]));
        [$ax, $ay] = $this->proyectar($a, $punto, $coseno);
        [$bx, $by] = $this->proyectar($b, $punto, $coseno);

        $dx = $bx - $ax;
        $dy = $by - $ay;
        $largo2 = $dx * $dx + $dy * $dy;

        $t = $largo2 > 0 ? max(0.0, min(1.0, -($ax * $dx + $ay * $dy) / $largo2)) : 0.0;

        return hypot($ax + $t * $dx, $ay + $t * $dy);
    }

    private function proyectar(array $punto, array $origen, float $coseno): array
    {
        return [
            deg2rad($punto[1] - $origen[1]) * $coseno * self::RADIO_TIERRA_M,
            deg2rad($punto[0] - $origen[0]) * self::RADIO_TIERRA_M,
        ];
    }

    private function segmentoMasCercano(array $punto, array $trazado): int
    {
        $indice = 0;
        $minima = INF;

        for ($i = 0, $total = count($trazado); $i < $total - 1; $i++) {
            $distancia = $this->distanciaPuntoSegmentoMetros($punto, $trazado[$i], $trazado[$i + 1]);
            if ($distancia < $minima) {
                $minima = $distancia;
                $indice = $i;
            }
        }

        return $indice;
    }

    private function insertarTramo(array $referencia, array $tramo): array
    {
        if (count($referencia) < 2) {
            return array_merge($referencia, $tramo);
        }

        $inicio = $this->segmentoMasCercano($tramo[0], $referencia);
        $fin = $this->segmentoMasCercano($tramo[count($tramo) - 1], $referencia);

        if ($fin < $inicio) {
            return $referencia;
        }

        return array_merge(
            array_slice($referencia, 0, $inicio + 1),
            $tramo,
            array_slice($referencia, $fin + 1)
        );
    }

    private function logsDelViaje(int $viajeId): array
    {
        return DB::table('log_gps_transporte')
            ->where('vjt_id', $viajeId)
            ->whereNull('deleted_at')
            ->orderBy('registrado_en_lgt')
            ->orderBy('id_lgt')
            ->get(['lat_lgt', 'lng_lgt', 'es_desvio_lgt', 'tramo_desvio_lgt'])
            ->map(fn ($log) => [
                (float) $log->lat_lgt,
                (float) $log->lng_lgt,
                $log->es_desvio_lgt ? (int) ($log->tramo_desvio_lgt ?? 1) : null,
            ])
            ->all();
    }

    private function guardarVersion(int $rutaId, ?int $viajeId, array $puntos, bool $soloSiNoHayVigente): ?int
    {
        $version = DB::transaction(function () use ($rutaId, $viajeId, $puntos, $soloSiNoHayVigente) {
            DB::table('ruta_transporte')->where('id_rtr', $rutaId)->lockForUpdate()->first();

            if ($soloSiNoHayVigente && $this->versionVigente($rutaId) !== null) {
                return null;
            }

            $version = (int) DB::table('trazado_ruta_transporte')->where('rtr_id', $rutaId)->max('version_trt') + 1;
            $ahora = now();
            $usuarioId = Auth::id();

            DB::table('trazado_ruta_transporte')
                ->where('rtr_id', $rutaId)
                ->where('vigente_trt', true)
                ->update(['vigente_trt' => false, 'updated_at' => $ahora, 'updated_id' => $usuarioId]);

            $filas = [];
            foreach (array_values($puntos) as $orden => $punto) {
                $filas[] = [
                    'rtr_id'      => $rutaId,
                    'version_trt' => $version,
                    'orden_trt'   => $orden + 1,
                    'lat_trt'     => round($punto[0], 7),
                    'lng_trt'     => round($punto[1], 7),
                    'vjt_id'      => $viajeId,
                    'vigente_trt' => true,
                    'created_at'  => $ahora,
                    'updated_at'  => $ahora,
                    'created_id'  => $usuarioId,
                ];
            }

            foreach (array_chunk($filas, 500) as $lote) {
                DB::table('trazado_ruta_transporte')->insert($lote);
            }

            return $version;
        });

        Cache::forget(self::claveCache($rutaId));

        return $version;
    }

    private function guardarLog(BusViaje $viaje, array $punto, Carbon $registradoEn, ?int $tramo): void
    {
        $viaje->gpsLogs()->create([
            'local_id_lgt'      => $punto['local_id'],
            'lat_lgt'           => $punto['lat'],
            'lng_lgt'           => $punto['lng'],
            'velocidad_lgt'     => $punto['velocidad'] ?? 0,
            'heading_lgt'       => $punto['heading'] ?? null,
            'registrado_en_lgt' => $registradoEn,
            'origen_lgt'        => 'app_conductor',
            'es_desvio_lgt'     => $tramo !== null,
            'tramo_desvio_lgt'  => $tramo,
        ]);
    }

    private function acumulados(object $fila, float $lat, float $lng, float $velocidad, string $momento): array
    {
        $velocidad = min(max($velocidad, 0.0), self::VELOCIDAD_MAXIMA_COLUMNA);
        $puntos = (int) $fila->puntos_recibidos_vjt + 1;
        $promedio = (((float) $fila->velocidad_promedio_vjt * ($puntos - 1)) + $velocidad) / $puntos;

        $cambios = [
            'ultima_lat_vjt'          => round($lat, 7),
            'ultima_lng_vjt'          => round($lng, 7),
            'ultima_velocidad_vjt'    => $velocidad,
            'ultima_posicion_en_vjt'  => $momento,
            'ultima_recepcion_en_vjt' => now(),
            'puntos_recibidos_vjt'    => $puntos,
            'velocidad_max_vjt'       => max((float) $fila->velocidad_max_vjt, $velocidad),
            'velocidad_promedio_vjt'  => round(min($promedio, self::VELOCIDAD_MAXIMA_COLUMNA), 2),
        ];

        if ($fila->ultima_lat_vjt === null || $fila->ultima_lng_vjt === null) {
            return $cambios;
        }

        $velocidadAnterior = (float) ($fila->ultima_velocidad_vjt ?? 0);
        $umbralRalenti = (float) config('transporte.umbral_velocidad_ralenti_kmh', 3);
        $umbralCarretera = (float) config('transporte.umbral_velocidad_carretera_kmh', 45);

        if ($velocidadAnterior <= $umbralRalenti && $velocidad <= $umbralRalenti) {
            $segundos = abs(Carbon::parse($momento)->diffInSeconds(Carbon::parse(substr((string) $fila->ultima_posicion_en_vjt, 0, 19))));
            $horas = min($segundos / 3600, (float) config('transporte.ralenti_max_gap_horas', 0.5));
            $cambios['ralenti_horas_vjt'] = round((float) $fila->ralenti_horas_vjt + $horas, 4);

            return $cambios;
        }

        $tramoKm = $this->distanciaMetros((float) $fila->ultima_lat_vjt, (float) $fila->ultima_lng_vjt, $lat, $lng) / 1000;

        if (($velocidadAnterior + $velocidad) / 2 >= $umbralCarretera) {
            $cambios['distancia_carretera_km_vjt'] = round((float) $fila->distancia_carretera_km_vjt + $tramoKm, 3);
        } else {
            $cambios['distancia_urbana_km_vjt'] = round((float) $fila->distancia_urbana_km_vjt + $tramoKm, 3);
        }

        return $cambios;
    }

    private function toleranciaDesvio(): float
    {
        return (float) config('transporte.tolerancia_desvio_metros', 50);
    }
}
