<?php

namespace App\Support\Deportes;

use App\Models\Deporte\AsistenciaDeportiva;
use App\Models\Deporte\DeporteCalendario;
use App\Models\Deporte\DeporteHorario;
use App\Models\Deporte\InscripcionDeportiva;
use App\Models\Deporte\IntegranteEquipo;
use App\Models\Deporte\SesionAsistencia;
use App\Support\Auditoria\Bitacora;
use App\Support\Cuentas\Cedula;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ServicioAsistencia
{
    public const RESULTADO_MARCADA = 'marcada';

    public const RESULTADO_NO_INSCRITA = 'no_inscrita';

    public const RESULTADO_YA_MARCADA = 'ya_marcada';

    public const RESULTADO_NO_ENCONTRADA = 'no_encontrada';

    public const RESULTADO_INVALIDA = 'invalida';

    public const MAX_CEDULAS = 300;

    public static function bloque(SesionAsistencia $sesion): DeporteHorario
    {
        return DeporteHorario::query()->todasLasSedes()
            ->with(['calendario' => fn ($q) => $q->todasLasSedes()->with('programa'), 'actividad', 'espacio' => fn ($q) => $q->todasLasSedes(), 'torneos'])
            ->findOrFail($sesion->hde_id);
    }

    public static function sesionDe(DeporteHorario $bloque, $fecha): ?SesionAsistencia
    {
        return SesionAsistencia::query()->todasLasSedes()
            ->where('hde_id', $bloque->id_hde)
            ->where('fecha_sad', Carbon::parse($fecha)->toDateString())
            ->first();
    }

    public static function abrir(DeporteHorario $bloque, $fecha): SesionAsistencia
    {
        try {
            $fecha = Carbon::parse($fecha)->startOfDay();
        } catch (\Throwable $e) {
            throw ValidationException::withMessages(['fecha' => 'La fecha indicada no es válida.']);
        }

        if ($fecha->gt(Carbon::today())) {
            throw ValidationException::withMessages(['fecha' => 'Todavía no se puede tomar asistencia: el evento es el ' . $fecha->format('d/m/Y') . '.']);
        }

        $calendario = $bloque->calendario;

        if (! $calendario || ! $calendario->estaActiva() || ! in_array($bloque->estado_hde, DeporteHorario::ESTADOS_CON_EVENTOS, true)) {
            throw ValidationException::withMessages(['hde_id' => 'La actividad está cancelada; no se puede tomar asistencia.']);
        }

        if (! $calendario->aplicaEnFecha($fecha)) {
            throw ValidationException::withMessages(['fecha' => '"' . $calendario->nombre_cld . '" no tiene evento el ' . $fecha->format('d/m/Y') . '. Se realiza: ' . mb_strtolower($calendario->descripcionCuando()) . '.']);
        }

        if ($existente = static::sesionDe($bloque, $fecha)) {
            return $existente;
        }

        try {
            $sesion = DB::transaction(fn () => SesionAsistencia::create([
                'hde_id'      => $bloque->id_hde,
                'fecha_sad'   => $fecha->toDateString(),
                'cerrada_sad' => false,
                'status_id'   => 1,
            ]));
        } catch (QueryException $e) {
            if ($existente = static::sesionDe($bloque, $fecha)) {
                return $existente;
            }

            throw $e;
        }

        return $sesion->fresh();
    }

    public static function esperados(SesionAsistencia $sesion, ?DeporteHorario $bloque = null): Collection
    {
        $bloque ??= static::bloque($sesion);
        $calendario = $bloque->calendario;

        if ($calendario?->esTorneo()) {
            $torneos = $bloque->torneos->pluck('id_tde')->all();

            return IntegranteEquipo::query()->todasLasSedes()
                ->with(['persona', 'equipo' => fn ($q) => $q->todasLasSedes()])
                ->whereHas('equipo', fn ($q) => $q->todasLasSedes()->whereIn('tde_id', $torneos ?: [0]))
                ->get()
                ->filter(fn ($integrante) => $integrante->persona !== null)
                ->unique('per_id')
                ->mapWithKeys(fn ($integrante) => [(int) $integrante->per_id => (object) [
                    'per_id'  => (int) $integrante->per_id,
                    'ide_id'  => null,
                    'ied_id'  => (int) $integrante->id_ied,
                    'persona' => $integrante->persona,
                    'grupo'   => $integrante->equipo?->nombre_equipo_etd,
                ]]);
        }

        if (! $calendario?->con_inscripcion_cld) {
            return collect();
        }

        return InscripcionDeportiva::query()->todasLasSedes()
            ->with('persona')
            ->where('hde_id', $bloque->id_hde)
            ->where('estado_ide', '!=', InscripcionDeportiva::ESTADO_RETIRADO)
            ->get()
            ->filter(fn ($inscripcion) => $inscripcion->persona !== null)
            ->mapWithKeys(fn ($inscripcion) => [(int) $inscripcion->per_id => (object) [
                'per_id'  => (int) $inscripcion->per_id,
                'ide_id'  => (int) $inscripcion->id_ide,
                'ied_id'  => null,
                'persona' => $inscripcion->persona,
                'grupo'   => null,
            ]]);
    }

    public static function marcarCedulas(SesionAsistencia $sesion, ?string $texto): array
    {
        $cedulas = ServicioInscripcion::separarCedulas($texto);

        if ($cedulas === []) {
            throw ValidationException::withMessages(['cedulas' => 'Escribe al menos una cédula.']);
        }

        if (count($cedulas) > self::MAX_CEDULAS) {
            throw ValidationException::withMessages(['cedulas' => 'Puedes pegar como máximo ' . self::MAX_CEDULAS . ' cédulas a la vez.']);
        }

        return DB::transaction(function () use ($sesion, $cedulas) {
            $sesion = static::bloquearAbierta($sesion);
            $bloque = static::bloque($sesion);
            $usaInscritos = (bool) $bloque->calendario?->usaInscritos();
            $esperados = static::esperados($sesion, $bloque);
            $marcas = AsistenciaDeportiva::query()->todasLasSedes()->where('sad_id', $sesion->id_sad)->get()->keyBy('per_id');
            $resultados = [];
            $procesadas = [];

            foreach ($cedulas as $cedula) {
                $normalizada = Cedula::normalizar($cedula);

                if ($normalizada === null) {
                    $resultados[] = ['cedula' => $cedula, 'resultado' => self::RESULTADO_INVALIDA, 'nombre' => null, 'mensaje' => 'No es una cédula válida.'];

                    continue;
                }

                if (isset($procesadas[$normalizada])) {
                    continue;
                }

                $procesadas[$normalizada] = true;
                $persona = ServicioInscripcion::buscarPersona($normalizada);

                if (! $persona) {
                    $resultados[] = ['cedula' => $normalizada, 'resultado' => self::RESULTADO_NO_ENCONTRADA, 'nombre' => null, 'mensaje' => 'No hay ninguna persona registrada con esta cédula.'];

                    continue;
                }

                $nombre = ServicioInscripcion::nombre($persona);
                $marca = $marcas[$persona->id_per] ?? null;

                if ($marca && $marca->estado_asd === AsistenciaDeportiva::ESTADO_PRESENTE) {
                    $resultados[] = ['cedula' => $normalizada, 'resultado' => self::RESULTADO_YA_MARCADA, 'nombre' => $nombre, 'mensaje' => 'Ya estaba marcada como presente.'];

                    continue;
                }

                $esperado = $esperados[(int) $persona->id_per] ?? null;

                if ($marca) {
                    $marca->update(['estado_asd' => AsistenciaDeportiva::ESTADO_PRESENTE]);
                } else {
                    $marcas[$persona->id_per] = AsistenciaDeportiva::create([
                        'sad_id'     => $sesion->id_sad,
                        'per_id'     => $persona->id_per,
                        'ide_id'     => $esperado?->ide_id,
                        'ied_id'     => $esperado?->ied_id,
                        'estado_asd' => AsistenciaDeportiva::ESTADO_PRESENTE,
                        'status_id'  => 1,
                    ]);
                }

                if ($usaInscritos && ! $esperado) {
                    $resultados[] = ['cedula' => $normalizada, 'resultado' => self::RESULTADO_NO_INSCRITA, 'nombre' => $nombre, 'mensaje' => $bloque->esDeTorneo() ? 'Presente, pero no es integrante de ningún equipo del torneo.' : 'Presente, pero no estaba inscrita.'];

                    continue;
                }

                $resultados[] = ['cedula' => $normalizada, 'resultado' => self::RESULTADO_MARCADA, 'nombre' => $nombre, 'mensaje' => 'Marcada como presente.'];
            }

            return $resultados;
        });
    }

    public static function quitar(SesionAsistencia $sesion, int $asistenciaId): void
    {
        DB::transaction(function () use ($sesion, $asistenciaId) {
            $sesion = static::bloquearAbierta($sesion);

            $marca = AsistenciaDeportiva::query()->todasLasSedes()
                ->where('sad_id', $sesion->id_sad)
                ->whereKey($asistenciaId)
                ->first();

            if (! $marca) {
                throw ValidationException::withMessages(['asistencia' => 'Esa persona no está en la lista de presentes.']);
            }

            $marca->forceDelete();
        });
    }

    public static function cerrar(SesionAsistencia $sesion): SesionAsistencia
    {
        return DB::transaction(function () use ($sesion) {
            $sesion = static::bloquearAbierta($sesion);
            $bloque = static::bloque($sesion);
            $esperados = static::esperados($sesion, $bloque);
            $marcados = AsistenciaDeportiva::query()->todasLasSedes()->where('sad_id', $sesion->id_sad)->pluck('per_id')->map(fn ($id) => (int) $id)->all();
            $faltantes = 0;

            foreach ($esperados as $personaId => $esperado) {
                if (in_array((int) $personaId, $marcados, true)) {
                    continue;
                }

                AsistenciaDeportiva::create([
                    'sad_id'     => $sesion->id_sad,
                    'per_id'     => $esperado->per_id,
                    'ide_id'     => $esperado->ide_id,
                    'ied_id'     => $esperado->ied_id,
                    'estado_asd' => AsistenciaDeportiva::ESTADO_INASISTENTE,
                    'status_id'  => 1,
                ]);
                $faltantes++;
            }

            $sesion->forceFill([
                'cerrada_sad'        => true,
                'cerrada_en_sad'     => now(),
                'cerrada_por_use_id' => Auth::id(),
            ])->save();

            if ($bloque->calendario?->esEsporadica() && $bloque->calendario->con_inscripcion_cld) {
                foreach ($esperados as $personaId => $esperado) {
                    InscripcionDeportiva::query()->todasLasSedes()->whereKey($esperado->ide_id)->update([
                        'estado_ide' => in_array((int) $personaId, $marcados, true) ? InscripcionDeportiva::ESTADO_ASISTIO : InscripcionDeportiva::ESTADO_NO_ASISTIO,
                    ]);
                }
            }

            Bitacora::registrar(
                accion: 'cierre_asistencia_deportiva',
                modulo: 'deporte',
                tablaAfectada: 'sesion_asistencia_deportiva',
                registroId: $sesion->id_sad,
                datosModificados: [
                    'hde_id'       => $sesion->hde_id,
                    'fecha_sad'    => $sesion->fecha_sad->toDateString(),
                    'presentes'    => count($marcados),
                    'inasistentes' => $faltantes,
                ],
            );

            return $sesion->fresh();
        });
    }

    public static function reabrir(SesionAsistencia $sesion, ?string $motivo = null): SesionAsistencia
    {
        return DB::transaction(function () use ($sesion, $motivo) {
            $bloqueada = SesionAsistencia::query()->todasLasSedes()->whereKey($sesion->getKey())->lockForUpdate()->first();

            if (! $bloqueada) {
                throw ValidationException::withMessages(['sesion' => 'La asistencia indicada no existe.']);
            }

            if (! $bloqueada->cerrada_sad) {
                throw ValidationException::withMessages(['sesion' => 'Esta asistencia ya está abierta.']);
            }

            $bloque = static::bloque($bloqueada);
            $anteriores = [
                'cerrada_en_sad'     => $bloqueada->cerrada_en_sad?->format('Y-m-d H:i:s'),
                'cerrada_por_use_id' => $bloqueada->cerrada_por_use_id,
                'presentes'          => AsistenciaDeportiva::query()->todasLasSedes()->where('sad_id', $bloqueada->id_sad)->where('estado_asd', AsistenciaDeportiva::ESTADO_PRESENTE)->count(),
                'inasistentes'       => AsistenciaDeportiva::query()->todasLasSedes()->where('sad_id', $bloqueada->id_sad)->where('estado_asd', AsistenciaDeportiva::ESTADO_INASISTENTE)->count(),
            ];

            AsistenciaDeportiva::query()->todasLasSedes()
                ->where('sad_id', $bloqueada->id_sad)
                ->where('estado_asd', AsistenciaDeportiva::ESTADO_INASISTENTE)
                ->forceDelete();

            $bloqueada->forceFill([
                'cerrada_sad'        => false,
                'cerrada_en_sad'     => null,
                'cerrada_por_use_id' => null,
            ])->save();

            if ($bloque->calendario?->esEsporadica()) {
                InscripcionDeportiva::query()->todasLasSedes()
                    ->where('hde_id', $bloque->id_hde)
                    ->whereIn('estado_ide', [InscripcionDeportiva::ESTADO_ASISTIO, InscripcionDeportiva::ESTADO_NO_ASISTIO])
                    ->update(['estado_ide' => InscripcionDeportiva::ESTADO_INSCRITO]);
            }

            Bitacora::registrar(
                accion: 'reapertura_asistencia_deportiva',
                modulo: 'deporte',
                tablaAfectada: 'sesion_asistencia_deportiva',
                registroId: $bloqueada->id_sad,
                datosAnteriores: $anteriores + ['cerrada_sad' => true],
                datosModificados: ['cerrada_sad' => false, 'hde_id' => $bloqueada->hde_id, 'fecha_sad' => $bloqueada->fecha_sad->toDateString()],
                observacion: trim((string) $motivo) !== '' ? 'Motivo: ' . trim((string) $motivo) : 'Asistencia reabierta para corregirla.',
            );

            return $bloqueada->fresh();
        });
    }

    public static function estado(SesionAsistencia $sesion, ?DeporteHorario $bloque = null): array
    {
        $bloque ??= static::bloque($sesion);
        $esperados = static::esperados($sesion, $bloque);

        $marcas = AsistenciaDeportiva::query()->todasLasSedes()
            ->with('persona')
            ->where('sad_id', $sesion->id_sad)
            ->orderByDesc('updated_at')
            ->orderByDesc('id_asd')
            ->get();

        $presentes = $marcas->where('estado_asd', AsistenciaDeportiva::ESTADO_PRESENTE)->values();
        $usaInscritos = (bool) $bloque->calendario?->usaInscritos();

        return [
            'usa_inscritos'      => $usaInscritos,
            'cerrada'            => (bool) $sesion->cerrada_sad,
            'total_presentes'    => $presentes->count(),
            'presentes_inscritos' => $presentes->filter(fn ($marca) => isset($esperados[(int) $marca->per_id]))->count(),
            'total_inscritos'    => $esperados->count(),
            'presentes'          => $presentes->map(fn ($marca) => [
                'id'       => $marca->id_asd,
                'cedula'   => $marca->persona?->cedula_per,
                'nombre'   => $marca->persona ? ServicioInscripcion::nombre($marca->persona) : 'Persona eliminada',
                'inscrito' => isset($esperados[(int) $marca->per_id]),
            ])->values()->all(),
        ];
    }

    public static function reporteEvento(SesionAsistencia $sesion, ?DeporteHorario $bloque = null): array
    {
        $bloque ??= static::bloque($sesion);
        $esperados = static::esperados($sesion, $bloque);

        $marcas = AsistenciaDeportiva::query()->todasLasSedes()
            ->with('persona')
            ->where('sad_id', $sesion->id_sad)
            ->get();

        $ordenar = fn (Collection $lista) => $lista->sortBy(fn ($fila) => mb_strtolower(($fila->persona?->apellidos_per ?? '') . ' ' . ($fila->persona?->nombres_per ?? '')))->values();

        $presentes = $marcas->where('estado_asd', AsistenciaDeportiva::ESTADO_PRESENTE);
        $marcados = $presentes->pluck('per_id')->map(fn ($id) => (int) $id)->all();
        $usaInscritos = (bool) $bloque->calendario?->usaInscritos();

        $inscritosPresentes = $usaInscritos ? $presentes->filter(fn ($marca) => isset($esperados[(int) $marca->per_id])) : $presentes;
        $noInscritos = $usaInscritos ? $presentes->reject(fn ($marca) => isset($esperados[(int) $marca->per_id])) : collect();
        $inasistentes = $sesion->cerrada_sad
            ? $marcas->where('estado_asd', AsistenciaDeportiva::ESTADO_INASISTENTE)
            : $esperados->reject(fn ($esperado, $personaId) => in_array((int) $personaId, $marcados, true));

        return [
            'usa_inscritos'   => $usaInscritos,
            'cerrada'         => (bool) $sesion->cerrada_sad,
            'total_inscritos' => $esperados->count(),
            'presentes'       => $ordenar($inscritosPresentes),
            'no_inscritos'    => $ordenar($noInscritos),
            'inasistentes'    => $ordenar($inasistentes->values()),
        ];
    }

    public static function historial(DeporteCalendario $actividad): array
    {
        $bloques = DeporteHorario::query()->todasLasSedes()->withTrashed()->where('cld_id', $actividad->id_cld)->pluck('id_hde')->all();

        $sesiones = SesionAsistencia::query()->todasLasSedes()
            ->whereIn('hde_id', $bloques ?: [0])
            ->where('cerrada_sad', true)
            ->orderBy('fecha_sad')
            ->get();

        $filas = DB::table('asistencia_deportiva as a')
            ->join('sesion_asistencia_deportiva as s', 's.id_sad', '=', 'a.sad_id')
            ->join('persona as p', 'p.id_per', '=', 'a.per_id')
            ->whereIn('s.hde_id', $bloques ?: [0])
            ->where('s.cerrada_sad', true)
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
                $fila->sesiones = $fila->presentes + $fila->inasistentes;
                $fila->porcentaje = $fila->sesiones > 0 ? round($fila->presentes * 100 / $fila->sesiones, 1) : 0;

                return $fila;
            });

        return [
            'sesiones'        => $sesiones,
            'totalSesiones'   => $sesiones->count(),
            'filas'           => $filas,
        ];
    }

    private static function bloquearAbierta(SesionAsistencia $sesion): SesionAsistencia
    {
        $bloqueada = SesionAsistencia::query()->todasLasSedes()->whereKey($sesion->getKey())->lockForUpdate()->first();

        if (! $bloqueada) {
            throw ValidationException::withMessages(['sesion' => 'La asistencia indicada no existe.']);
        }

        if ($bloqueada->cerrada_sad) {
            throw ValidationException::withMessages(['sesion' => 'Esta asistencia ya está cerrada. Para corregirla, primero hay que reabrirla.']);
        }

        return $bloqueada;
    }
}
