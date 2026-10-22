<?php

namespace App\Support\Deportes;

use App\Models\Deporte\DeporteActividad;
use App\Models\Deporte\DeporteCalendario;
use App\Models\Deporte\DeporteEspacio;
use App\Models\Deporte\DeporteHorario;
use App\Support\Auditoria\Bitacora;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ServicioActividades
{
    public const MAX_BLOQUES = 12;

    public static function guardar(array $datos, array $bloques, ?DeporteCalendario $actividad = null): DeporteCalendario
    {
        $tipo = $datos['tipo_cld'] ?? DeporteCalendario::TIPO_ESPORADICA;

        if (! array_key_exists($tipo, DeporteCalendario::TIPOS)) {
            throw ValidationException::withMessages(['tipo_cld' => 'Elige si la actividad ocurre una sola vez o se repite cada semana.']);
        }

        [$inicio, $fin, $dias] = static::fechasDeCabecera($tipo, $datos);

        $cabecera = [
            'nombre_cld'          => trim((string) $datos['nombre_cld']),
            'descripcion_cld'     => $datos['descripcion_cld'] ?? null,
            'tipo_cld'            => $tipo,
            'fecha_inicio_cld'    => $inicio->toDateString(),
            'fecha_fin_cld'       => $fin->toDateString(),
            'dias_semana_cld'     => $dias === [] ? null : implode(',', $dias),
            'seccion_cld'         => trim((string) ($datos['seccion_cld'] ?? '')) ?: null,
            'prg_id'              => $datos['prg_id'] ?? null,
            'con_inscripcion_cld' => $tipo === DeporteCalendario::TIPO_TORNEO ? false : (bool) ($datos['con_inscripcion_cld'] ?? false),
            'sed_id'              => (int) $datos['sed_id'],
        ];

        $modelo = $actividad ? $actividad->replicate() : new DeporteCalendario();
        $modelo->fill($cabecera);
        $fechas = $modelo->fechas();

        if ($fechas === []) {
            throw ValidationException::withMessages(['dias' => 'Con los días elegidos no hay ninguna fecha entre el ' . $inicio->format('d/m/Y') . ' y el ' . $fin->format('d/m/Y') . '.']);
        }

        $existentes = $actividad
            ? DeporteHorario::query()->todasLasSedes()->where('cld_id', $actividad->id_cld)->get()->keyBy('id_hde')
            : collect();

        $normalizados = static::validarBloques($bloques, $cabecera['sed_id'], $existentes, $fechas);
        $conservados = array_filter(array_column($normalizados, 'id'));

        foreach ($existentes as $id => $existente) {
            if (in_array((int) $id, $conservados, true) || $existente->estado_hde === DeporteHorario::ESTADO_CANCELADO) {
                continue;
            }

            if ($existente->inscripcionesVigentes()->exists() || $existente->sesiones()->exists() || $existente->torneos()->exists()) {
                throw ValidationException::withMessages(['bloques' => 'No puedes quitar el bloque de ' . ($existente->actividad?->nombre_acd ?? 'la disciplina') . ' (' . $existente->horas() . ') porque ya tiene inscritos o asistencia registrada.']);
            }
        }

        return DB::transaction(function () use ($actividad, $cabecera, $normalizados, $existentes, $conservados, $tipo, $inicio) {
            if ($actividad) {
                $actividad->update($cabecera);
            } else {
                $actividad = DeporteCalendario::create($cabecera + [
                    'use_id'    => Auth::id(),
                    'status_id' => 1,
                ]);
            }

            foreach ($existentes as $id => $existente) {
                if (! in_array((int) $id, $conservados, true) && $existente->estado_hde !== DeporteHorario::ESTADO_CANCELADO) {
                    $existente->delete();
                }
            }

            foreach ($normalizados as $bloque) {
                $valores = [
                    'acd_id'               => $bloque['acd_id'],
                    'epd_id'               => $bloque['epd_id'],
                    'hora_inicio_hde'      => $bloque['hora_inicio'] . ':00',
                    'hora_fin_hde'         => $bloque['hora_fin'] . ':00',
                    'cupo_maximo_hde'      => $bloque['cupo'],
                    'dia_semana_hde'       => null,
                    'fecha_especifica_hde' => $tipo === DeporteCalendario::TIPO_ESPORADICA ? $inicio->toDateString() : null,
                ];

                if ($bloque['id']) {
                    $existentes[$bloque['id']]->update($valores);

                    continue;
                }

                DeporteHorario::create($valores + [
                    'cld_id'     => $actividad->id_cld,
                    'use_id'     => Auth::id() ?? $actividad->use_id,
                    'estado_hde' => DeporteHorario::ESTADO_ACTIVO,
                    'status_id'  => 1,
                ]);
            }

            return $actividad->fresh(['horarios']);
        });
    }

    public static function cancelar(DeporteCalendario $actividad, ?string $motivo = null): void
    {
        $motivo = trim((string) $motivo) ?: 'Cancelada por la coordinación de deportes.';

        DB::transaction(function () use ($actividad, $motivo) {
            $actividad->update(['status_id' => 2]);

            DeporteHorario::query()->todasLasSedes()
                ->where('cld_id', $actividad->id_cld)
                ->where('estado_hde', DeporteHorario::ESTADO_ACTIVO)
                ->get()
                ->each(fn (DeporteHorario $bloque) => $bloque->update([
                    'estado_hde'             => DeporteHorario::ESTADO_CANCELADO,
                    'motivo_cancelacion_hde' => $motivo,
                ]));
        });

        Bitacora::registrar(
            accion: 'cancelacion_actividad_deportiva',
            modulo: 'deporte',
            tablaAfectada: 'calendario_deportivo',
            registroId: $actividad->id_cld,
            datosModificados: ['status_id' => 2, 'motivo' => $motivo],
        );
    }

    public static function activar(DeporteCalendario $actividad): void
    {
        $bloques = DeporteHorario::query()->todasLasSedes()
            ->with('espacio')
            ->where('cld_id', $actividad->id_cld)
            ->where('estado_hde', DeporteHorario::ESTADO_CANCELADO)
            ->get();

        $fechas = $actividad->fechas();

        foreach ($bloques as $bloque) {
            $choque = DeporteHorario::buscarChoque((int) $bloque->epd_id, $fechas, $bloque->hora_inicio_hde, $bloque->hora_fin_hde, $bloques->pluck('id_hde')->all());

            if ($choque) {
                throw ValidationException::withMessages(['actividad' => 'No se puede reactivar. ' . DeporteHorario::mensajeChoque($choque, $bloque->espacio?->nombre_epd)]);
            }
        }

        DB::transaction(function () use ($actividad, $bloques) {
            $actividad->update(['status_id' => 1]);

            $bloques->each(fn (DeporteHorario $bloque) => $bloque->update([
                'estado_hde'             => DeporteHorario::ESTADO_ACTIVO,
                'motivo_cancelacion_hde' => null,
            ]));
        });
    }

    public static function bloquesDesdeFormulario($entrada): array
    {
        $bloques = [];

        foreach ((array) $entrada as $bloque) {
            if (! is_array($bloque)) {
                continue;
            }

            $vacio = empty($bloque['acd_id']) && empty($bloque['epd_id']) && empty($bloque['hora_inicio']) && empty($bloque['hora_fin']);

            if ($vacio && empty($bloque['id'])) {
                continue;
            }

            $bloques[] = [
                'id'          => isset($bloque['id']) && is_numeric($bloque['id']) ? (int) $bloque['id'] : null,
                'acd_id'      => isset($bloque['acd_id']) && is_numeric($bloque['acd_id']) ? (int) $bloque['acd_id'] : null,
                'epd_id'      => isset($bloque['epd_id']) && is_numeric($bloque['epd_id']) ? (int) $bloque['epd_id'] : null,
                'hora_inicio' => isset($bloque['hora_inicio']) ? substr(trim((string) $bloque['hora_inicio']), 0, 5) : '',
                'hora_fin'    => isset($bloque['hora_fin']) ? substr(trim((string) $bloque['hora_fin']), 0, 5) : '',
                'cupo'        => isset($bloque['cupo']) && $bloque['cupo'] !== '' && $bloque['cupo'] !== null ? $bloque['cupo'] : null,
            ];
        }

        return $bloques;
    }

    private static function fechasDeCabecera(string $tipo, array $datos): array
    {
        try {
            if ($tipo === DeporteCalendario::TIPO_ESPORADICA) {
                if (empty($datos['fecha'])) {
                    throw ValidationException::withMessages(['fecha' => 'Indica la fecha del evento.']);
                }

                $fecha = Carbon::parse($datos['fecha'])->startOfDay();

                return [$fecha, $fecha->copy(), []];
            }

            if (empty($datos['fecha_inicio_cld']) || empty($datos['fecha_fin_cld'])) {
                throw ValidationException::withMessages(['fecha_inicio_cld' => 'Indica desde qué fecha y hasta qué fecha se realiza.']);
            }

            $inicio = Carbon::parse($datos['fecha_inicio_cld'])->startOfDay();
            $fin = Carbon::parse($datos['fecha_fin_cld'])->startOfDay();
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw ValidationException::withMessages(['fecha' => 'Alguna de las fechas no es válida.']);
        }

        if ($fin->lt($inicio)) {
            throw ValidationException::withMessages(['fecha_fin_cld' => 'La fecha final debe ser igual o posterior a la fecha de inicio.']);
        }

        if ($inicio->diffInDays($fin) > DeporteCalendario::MAX_DIAS_RANGO) {
            throw ValidationException::withMessages(['fecha_fin_cld' => 'El rango de fechas no puede pasar de un año.']);
        }

        $dias = DeporteCalendario::normalizarDias($datos['dias'] ?? []);

        if ($tipo === DeporteCalendario::TIPO_RECURRENTE && $dias === []) {
            throw ValidationException::withMessages(['dias' => 'Marca al menos un día de la semana en que se repite la actividad.']);
        }

        return [$inicio, $fin, $dias];
    }

    private static function validarBloques(array $bloques, int $sedeId, $existentes, array $fechas): array
    {
        if ($bloques === []) {
            throw ValidationException::withMessages(['bloques' => 'Agrega al menos un bloque: disciplina, espacio y hora.']);
        }

        if (count($bloques) > self::MAX_BLOQUES) {
            throw ValidationException::withMessages(['bloques' => 'Una actividad puede tener como máximo ' . self::MAX_BLOQUES . ' bloques.']);
        }

        $disciplinas = DeporteActividad::query()->whereIn('id_acd', array_filter(array_column($bloques, 'acd_id')) ?: [0])->get()->keyBy('id_acd');
        $espacios = DeporteEspacio::query()->todasLasSedes()->whereIn('id_epd', array_filter(array_column($bloques, 'epd_id')) ?: [0])->get()->keyBy('id_epd');
        $normalizados = [];

        foreach ($bloques as $indice => $bloque) {
            $n = $indice + 1;
            $previo = $bloque['id'] ? ($existentes[$bloque['id']] ?? null) : null;

            if ($bloque['id'] && ! $previo) {
                throw ValidationException::withMessages(['bloques' => "Bloque {$n}: no pertenece a esta actividad."]);
            }

            $disciplina = $disciplinas[$bloque['acd_id']] ?? null;

            if (! $disciplina || ((int) $disciplina->status_id !== 1 && (int) $previo?->acd_id !== (int) $disciplina->id_acd)) {
                throw ValidationException::withMessages(['bloques' => "Bloque {$n}: elige una disciplina activa."]);
            }

            $espacio = $espacios[$bloque['epd_id']] ?? null;

            if (! $espacio || ((int) $espacio->status_id !== 1 && (int) $previo?->epd_id !== (int) $espacio->id_epd)) {
                throw ValidationException::withMessages(['bloques' => "Bloque {$n}: elige un espacio activo."]);
            }

            if ((int) $espacio->sed_id !== $sedeId) {
                throw ValidationException::withMessages(['bloques' => "Bloque {$n}: el espacio {$espacio->nombre_epd} es de otra sede."]);
            }

            if (! preg_match('/^\d{2}:\d{2}$/', $bloque['hora_inicio']) || ! preg_match('/^\d{2}:\d{2}$/', $bloque['hora_fin'])) {
                throw ValidationException::withMessages(['bloques' => "Bloque {$n}: indica la hora de inicio y la hora de fin."]);
            }

            if ($bloque['hora_inicio'] >= $bloque['hora_fin']) {
                throw ValidationException::withMessages(['bloques' => "Bloque {$n}: la hora de fin debe ser después de la hora de inicio."]);
            }

            $cupo = $bloque['cupo'];

            if ($cupo !== null && (! is_numeric($cupo) || (int) $cupo < 1 || (int) $cupo != $cupo)) {
                throw ValidationException::withMessages(['bloques' => "Bloque {$n}: el cupo debe ser un número entero mayor que cero, o dejarse vacío si no hay límite."]);
            }

            $cupo = $cupo === null ? null : (int) $cupo;

            if ($previo && $cupo !== null && ($inscritos = $previo->cuposOcupados()) > $cupo) {
                throw ValidationException::withMessages(['bloques' => "Bloque {$n}: el cupo no puede ser menor que los {$inscritos} inscritos actuales."]);
            }

            $normalizados[] = $bloque + ['nombre_espacio' => $espacio->nombre_epd, 'nombre_disciplina' => $disciplina->nombre_acd, 'cupo_normalizado' => $cupo];
        }

        foreach ($normalizados as $i => $a) {
            foreach ($normalizados as $j => $b) {
                if ($j <= $i || $a['epd_id'] !== $b['epd_id']) {
                    continue;
                }

                if (DeporteHorario::seSolapan($a['hora_inicio'], $a['hora_fin'], $b['hora_inicio'], $b['hora_fin'])) {
                    throw ValidationException::withMessages(['bloques' => 'Los bloques ' . ($i + 1) . ' y ' . ($j + 1) . ' usan ' . $a['nombre_espacio'] . ' a la misma hora. Cada bloque simultáneo necesita un espacio distinto.']);
                }
            }
        }

        $excluir = $existentes->keys()->map(fn ($id) => (int) $id)->all();

        foreach ($normalizados as $i => $bloque) {
            $choque = DeporteHorario::buscarChoque($bloque['epd_id'], $fechas, $bloque['hora_inicio'], $bloque['hora_fin'], $excluir);

            if ($choque) {
                throw ValidationException::withMessages(['bloques' => 'Bloque ' . ($i + 1) . ': ' . DeporteHorario::mensajeChoque($choque, $bloque['nombre_espacio'])]);
            }
        }

        return array_map(fn ($bloque) => [
            'id'          => $bloque['id'],
            'acd_id'      => $bloque['acd_id'],
            'epd_id'      => $bloque['epd_id'],
            'hora_inicio' => $bloque['hora_inicio'],
            'hora_fin'    => $bloque['hora_fin'],
            'cupo'        => $bloque['cupo_normalizado'],
        ], $normalizados);
    }
}
