<?php

namespace App\Support\Deportes;

use App\Models\Deporte\AsistenciaDeportiva;
use App\Models\Deporte\DeporteHorario;
use App\Models\Deporte\InscripcionDeportiva;
use App\Models\Deporte\SesionAsistencia;
use App\Support\Auditoria\Bitacora;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ServicioAsistencia
{
    public static function sesionDe(DeporteHorario $horario, $fecha): ?SesionAsistencia
    {
        return SesionAsistencia::query()->todasLasSedes()
            ->where('hde_id', $horario->id_hde)
            ->whereDate('fecha_sad', Carbon::parse($fecha)->toDateString())
            ->first();
    }

    public static function abrir(DeporteHorario $horario, $fecha): SesionAsistencia
    {
        $fecha = Carbon::parse($fecha)->startOfDay();

        if ($fecha->gt(Carbon::today())) {
            throw ValidationException::withMessages(['fecha' => 'No se puede tomar asistencia de una fecha futura.']);
        }

        if ($horario->estado_hde === DeporteHorario::ESTADO_CANCELADO) {
            throw ValidationException::withMessages(['hde_id' => 'El horario está cancelado.']);
        }

        if (! $horario->aplicaEnFecha($fecha)) {
            throw ValidationException::withMessages(['fecha' => 'El horario no tiene sesión el ' . $fecha->format('d/m/Y') . ': se dicta ' . mb_strtolower($horario->descripcionCuando()) . '.']);
        }

        if ($existente = static::sesionDe($horario, $fecha)) {
            return $existente;
        }

        $sesion = SesionAsistencia::create([
            'hde_id'      => $horario->id_hde,
            'fecha_sad'   => $fecha->toDateString(),
            'cerrada_sad' => false,
            'status_id'   => 1,
        ]);

        return $sesion->fresh();
    }

    public static function inscritosDeSesion(SesionAsistencia $sesion): Collection
    {
        return InscripcionDeportiva::query()->todasLasSedes()
            ->with('persona')
            ->where('hde_id', $sesion->hde_id)
            ->where('estado_ide', '!=', InscripcionDeportiva::ESTADO_RETIRADO)
            ->whereDate('fecha_registro_ide', '<=', Carbon::parse($sesion->fecha_sad)->toDateString())
            ->get()
            ->sortBy(fn ($inscripcion) => mb_strtolower(($inscripcion->persona?->apellidos_per ?? '') . ' ' . ($inscripcion->persona?->nombres_per ?? '')))
            ->values();
    }

    public static function marcar(SesionAsistencia $sesion, array $idsPresentes): void
    {
        DB::transaction(function () use ($sesion, $idsPresentes) {
            $sesion = static::bloquearAbierta($sesion);
            static::guardarMarcas($sesion, $idsPresentes);
        });
    }

    public static function cerrar(SesionAsistencia $sesion, ?array $idsPresentes = null): SesionAsistencia
    {
        return DB::transaction(function () use ($sesion, $idsPresentes) {
            $sesion = static::bloquearAbierta($sesion);

            if ($idsPresentes !== null) {
                static::guardarMarcas($sesion, $idsPresentes);
            }

            $marcados = AsistenciaDeportiva::query()->todasLasSedes()
                ->where('sad_id', $sesion->id_sad)
                ->pluck('ide_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $inscritos = static::inscritosDeSesion($sesion);
            $faltantes = 0;

            foreach ($inscritos as $inscripcion) {
                if (! in_array((int) $inscripcion->id_ide, $marcados, true)) {
                    AsistenciaDeportiva::create([
                        'sad_id'     => $sesion->id_sad,
                        'ide_id'     => $inscripcion->id_ide,
                        'estado_asd' => AsistenciaDeportiva::ESTADO_INASISTENTE,
                        'status_id'  => 1,
                    ]);
                    $faltantes++;
                }
            }

            $sesion->forceFill([
                'cerrada_sad'        => true,
                'cerrada_en_sad'     => now(),
                'cerrada_por_use_id' => Auth::id(),
            ])->save();

            $horario = DeporteHorario::query()->todasLasSedes()->find($sesion->hde_id);

            if ($horario && $horario->esFechaEspecifica()) {
                foreach ($inscritos as $inscripcion) {
                    $inscripcion->estado_ide = in_array((int) $inscripcion->id_ide, $marcados, true)
                        ? InscripcionDeportiva::ESTADO_ASISTIO
                        : InscripcionDeportiva::ESTADO_NO_ASISTIO;
                    $inscripcion->save();
                }
            }

            Bitacora::registrar(
                accion: 'cierre_asistencia_deportiva',
                modulo: 'deporte',
                tablaAfectada: 'sesion_asistencia_deportiva',
                registroId: $sesion->id_sad,
                datosModificados: [
                    'hde_id'       => $sesion->hde_id,
                    'fecha_sad'    => Carbon::parse($sesion->fecha_sad)->toDateString(),
                    'presentes'    => count($marcados),
                    'inasistentes' => $faltantes,
                ],
            );

            return $sesion->fresh();
        });
    }

    public static function reporte(DeporteHorario $horario, $desde, $hasta): array
    {
        $desde = Carbon::parse($desde)->toDateString();
        $hasta = Carbon::parse($hasta)->toDateString();

        $sesiones = DB::table('sesion_asistencia_deportiva')
            ->where('hde_id', $horario->id_hde)
            ->whereNull('deleted_at')
            ->whereBetween('fecha_sad', [$desde, $hasta]);

        $totalCerradas = (clone $sesiones)->where('cerrada_sad', true)->count();
        $totalAbiertas = (clone $sesiones)->where('cerrada_sad', false)->count();

        $filas = DB::table('asistencia_deportiva as a')
            ->join('sesion_asistencia_deportiva as s', 's.id_sad', '=', 'a.sad_id')
            ->join('inscripcion_deportiva as i', 'i.id_ide', '=', 'a.ide_id')
            ->join('persona as p', 'p.id_per', '=', 'i.per_id')
            ->where('s.hde_id', $horario->id_hde)
            ->where('s.cerrada_sad', true)
            ->whereBetween('s.fecha_sad', [$desde, $hasta])
            ->whereNull('a.deleted_at')
            ->whereNull('s.deleted_at')
            ->groupBy('p.id_per', 'p.cedula_per', 'p.nombres_per', 'p.apellidos_per')
            ->orderBy('p.apellidos_per')
            ->orderBy('p.nombres_per')
            ->select('p.id_per', 'p.cedula_per', 'p.nombres_per', 'p.apellidos_per')
            ->selectRaw("SUM(CASE WHEN a.estado_asd = 'presente' THEN 1 ELSE 0 END) AS presentes")
            ->selectRaw("SUM(CASE WHEN a.estado_asd = 'inasistente' THEN 1 ELSE 0 END) AS inasistentes")
            ->get()
            ->map(function ($fila) {
                $fila->presentes = (int) $fila->presentes;
                $fila->inasistentes = (int) $fila->inasistentes;
                $total = $fila->presentes + $fila->inasistentes;
                $fila->porcentaje = $total > 0 ? round($fila->presentes * 100 / $total, 1) : 0;

                return $fila;
            });

        return [
            'filas'           => $filas,
            'sesionesCerradas' => $totalCerradas,
            'sesionesAbiertas' => $totalAbiertas,
            'totalPresentes'  => $filas->sum('presentes'),
            'totalInasistentes' => $filas->sum('inasistentes'),
        ];
    }

    private static function bloquearAbierta(SesionAsistencia $sesion): SesionAsistencia
    {
        $bloqueada = SesionAsistencia::query()->todasLasSedes()->whereKey($sesion->getKey())->lockForUpdate()->first();

        if (! $bloqueada) {
            throw ValidationException::withMessages(['sesion' => 'La sesión de asistencia no existe.']);
        }

        if ($bloqueada->cerrada_sad) {
            throw ValidationException::withMessages(['sesion' => 'La sesión ya está cerrada y no se puede modificar.']);
        }

        return $bloqueada;
    }

    private static function guardarMarcas(SesionAsistencia $sesion, array $idsPresentes): void
    {
        $validos = static::inscritosDeSesion($sesion)->pluck('id_ide')->map(fn ($id) => (int) $id)->all();
        $presentes = array_values(array_intersect($validos, array_map('intval', $idsPresentes)));

        AsistenciaDeportiva::query()->todasLasSedes()
            ->where('sad_id', $sesion->id_sad)
            ->when($presentes, fn ($q) => $q->whereNotIn('ide_id', $presentes))
            ->forceDelete();

        $existentes = AsistenciaDeportiva::query()->todasLasSedes()
            ->where('sad_id', $sesion->id_sad)
            ->pluck('ide_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach (array_diff($presentes, $existentes) as $idInscripcion) {
            AsistenciaDeportiva::create([
                'sad_id'     => $sesion->id_sad,
                'ide_id'     => $idInscripcion,
                'estado_asd' => AsistenciaDeportiva::ESTADO_PRESENTE,
                'status_id'  => 1,
            ]);
        }
    }
}
