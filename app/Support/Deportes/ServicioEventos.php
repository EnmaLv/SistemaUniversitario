<?php

namespace App\Support\Deportes;

use App\Models\Deporte\AsistenciaDeportiva;
use App\Models\Deporte\DeporteHorario;
use App\Models\Deporte\SesionAsistencia;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ServicioEventos
{
    public static function listar($desde, $hasta, ?string $buscar = null, bool $recientesPrimero = true): Collection
    {
        $desde = Carbon::parse($desde)->startOfDay();
        $hasta = Carbon::parse($hasta)->startOfDay();

        if ($desde->gt($hasta)) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        $bloques = DeporteHorario::query()
            ->with(['calendario.programa', 'actividad', 'espacio', 'torneos'])
            ->whereIn('estado_hde', DeporteHorario::ESTADOS_CON_EVENTOS)
            ->whereHas('calendario', fn ($q) => $q
                ->where('status_id', 1)
                ->where('fecha_inicio_cld', '<=', $hasta->toDateString())
                ->where('fecha_fin_cld', '>=', $desde->toDateString()))
            ->get();

        if ($bloques->isEmpty()) {
            return collect();
        }

        $sesiones = SesionAsistencia::query()
            ->whereIn('hde_id', $bloques->pluck('id_hde')->all())
            ->where('fecha_sad', '>=', $desde->toDateString())
            ->where('fecha_sad', '<=', $hasta->toDateString())
            ->withCount(['asistencias as presentes_count' => fn ($q) => $q->where('estado_asd', AsistenciaDeportiva::ESTADO_PRESENTE)])
            ->get()
            ->keyBy(fn (SesionAsistencia $sesion) => $sesion->hde_id . '|' . $sesion->fecha_sad->toDateString());

        $texto = mb_strtolower(trim((string) $buscar));
        $eventos = collect();

        foreach ($bloques as $bloque) {
            $calendario = $bloque->calendario;
            $torneo = $bloque->torneos->sortByDesc('id_tde')->first();
            $nombre = $torneo?->nombre_tde ?? $calendario->nombre_cld;
            $seccion = $calendario->seccionLegible();

            if ($texto !== '') {
                $pajar = mb_strtolower(implode(' ', [$nombre, $calendario->nombre_cld, $bloque->actividad?->nombre_acd, $bloque->espacio?->nombre_epd, $seccion]));

                if (! str_contains($pajar, $texto)) {
                    continue;
                }
            }

            foreach ($calendario->fechasEntre($desde, $hasta) as $fecha) {
                $eventos->push((object) [
                    'bloque'     => $bloque,
                    'actividad'  => $calendario,
                    'torneo'     => $torneo,
                    'nombre'     => $nombre,
                    'disciplina' => $bloque->actividad?->nombre_acd ?? '-',
                    'espacio'    => $bloque->espacio?->nombre_epd ?? '-',
                    'seccion'    => $seccion,
                    'fecha'      => Carbon::parse($fecha),
                    'horas'      => $bloque->horas(),
                    'inicio'     => $fecha . ' ' . $bloque->horaInicio('H:i'),
                    'sesion'     => $sesiones[$bloque->id_hde . '|' . $fecha] ?? null,
                ]);
            }
        }

        $ordenados = $eventos->sortBy('inicio')->values();

        if (! $recientesPrimero) {
            return $ordenados;
        }

        return $ordenados->groupBy(fn ($evento) => $evento->fecha->toDateString())
            ->sortKeysDesc()
            ->flatten(1)
            ->values();
    }

    public static function estadoDe(object $evento): string
    {
        if (! $evento->sesion) {
            return 'sin_tomar';
        }

        return $evento->sesion->cerrada_sad ? 'cerrada' : 'abierta';
    }
}
