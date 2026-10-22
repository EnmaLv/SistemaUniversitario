<?php

namespace App\Support\Deportes;

use App\Models\Deporte\DeporteHorario;
use App\Models\Deporte\InscripcionDeportiva;
use App\Models\Persona;
use App\Support\Auditoria\Bitacora;
use App\Support\Cuentas\Cedula;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ServicioInscripcion
{
    public static function buscarPersona(?string $cedula): ?Persona
    {
        $normalizada = Cedula::normalizar($cedula);

        if ($normalizada === null) {
            return null;
        }

        $variantes = [$normalizada];

        foreach (['V', 'E'] as $prefijo) {
            $variantes[] = $prefijo . $normalizada;
            $variantes[] = $prefijo . '-' . $normalizada;
        }

        return Persona::query()
            ->whereNull('deleted_at')
            ->whereIn('cedula_per', $variantes)
            ->orderBy('id_per')
            ->first();
    }

    public static function personaPorCedula(?string $cedula, string $campo = 'cedula'): Persona
    {
        if (Cedula::normalizar($cedula) === null) {
            throw ValidationException::withMessages([$campo => 'La cédula "' . trim((string) $cedula) . '" no es válida. Escríbela solo con números, por ejemplo 12345678.']);
        }

        $persona = static::buscarPersona($cedula);

        if (! $persona) {
            throw ValidationException::withMessages([$campo => 'No hay ninguna persona registrada con la cédula ' . Cedula::normalizar($cedula) . '. Pide que la registren primero en Personas.']);
        }

        if ((int) $persona->status_id !== 1) {
            throw ValidationException::withMessages([$campo => 'La persona con la cédula ' . $persona->cedula_per . ' está inactiva en el sistema.']);
        }

        return $persona;
    }

    public static function separarCedulas(?string $texto): array
    {
        $resultado = [];

        foreach (preg_split('/[\r\n,;\t]+/u', (string) $texto) ?: [] as $trozo) {
            $trozo = trim($trozo);

            if ($trozo === '') {
                continue;
            }

            if (Cedula::normalizar($trozo) === null && preg_match('/\s/u', $trozo)) {
                foreach (preg_split('/\s+/u', $trozo) ?: [] as $parte) {
                    if (trim($parte) !== '') {
                        $resultado[] = trim($parte);
                    }
                }

                continue;
            }

            $resultado[] = $trozo;
        }

        return $resultado;
    }

    public static function inscribir(DeporteHorario $horario, ?string $cedula, ?string $observaciones = null): InscripcionDeportiva
    {
        $persona = static::personaPorCedula($cedula);

        return DB::transaction(function () use ($horario, $persona, $observaciones) {
            $bloqueado = DeporteHorario::query()->todasLasSedes()
                ->with(['calendario' => fn ($q) => $q->todasLasSedes(), 'actividad'])
                ->whereKey($horario->getKey())
                ->lockForUpdate()
                ->first();

            $actividad = $bloqueado?->calendario;

            if (! $bloqueado || ! $actividad) {
                throw ValidationException::withMessages(['hde_id' => 'La actividad elegida no existe.']);
            }

            if ($actividad->esTorneo()) {
                throw ValidationException::withMessages(['hde_id' => 'En los torneos no hay inscripción individual: agrega a la persona como integrante de un equipo desde la pantalla del torneo.']);
            }

            if (! $actividad->con_inscripcion_cld) {
                throw ValidationException::withMessages(['hde_id' => 'La actividad "' . $actividad->nombre_cld . '" es abierta: no lleva inscripción. Solo se toma la asistencia el día del evento.']);
            }

            if (! $actividad->estaActiva() || ! $bloqueado->estaActivo()) {
                throw ValidationException::withMessages(['hde_id' => 'La actividad está cancelada o terminada y ya no admite inscripciones.']);
            }

            $existente = InscripcionDeportiva::query()->todasLasSedes()->withTrashed()
                ->where('hde_id', $bloqueado->id_hde)
                ->where('per_id', $persona->id_per)
                ->first();

            if ($existente && ! $existente->trashed() && $existente->estaVigente()) {
                throw ValidationException::withMessages(['cedula' => static::nombre($persona) . ' ya está inscrito(a) en este bloque.']);
            }

            $simultanea = InscripcionDeportiva::query()->todasLasSedes()
                ->with(['horario' => fn ($q) => $q->todasLasSedes()->with('actividad')])
                ->where('per_id', $persona->id_per)
                ->where('estado_ide', '!=', InscripcionDeportiva::ESTADO_RETIRADO)
                ->whereHas('horario', fn ($q) => $q->todasLasSedes()
                    ->where('cld_id', $actividad->id_cld)
                    ->where('id_hde', '!=', $bloqueado->id_hde)
                    ->where('estado_hde', DeporteHorario::ESTADO_ACTIVO)
                    ->where('hora_inicio_hde', '<', $bloqueado->hora_fin_hde)
                    ->where('hora_fin_hde', '>', $bloqueado->hora_inicio_hde))
                ->first();

            if ($simultanea) {
                throw ValidationException::withMessages(['cedula' => static::nombre($persona) . ' ya está inscrito(a) en ' . ($simultanea->horario?->actividad?->nombre_acd ?? 'otro bloque') . ', que ocurre a la misma hora en esta actividad.']);
            }

            $ocupados = InscripcionDeportiva::query()->todasLasSedes()
                ->where('hde_id', $bloqueado->id_hde)
                ->where('estado_ide', '!=', InscripcionDeportiva::ESTADO_RETIRADO)
                ->count();

            if ($bloqueado->cupo_maximo_hde !== null && $ocupados >= $bloqueado->cupo_maximo_hde) {
                throw ValidationException::withMessages(['cedula' => 'Ya no hay cupos: este bloque tiene ' . $ocupados . ' de ' . $bloqueado->cupo_maximo_hde . ' inscritos.']);
            }

            $datos = [
                'fecha_registro_ide' => now()->toDateString(),
                'estado_ide'         => InscripcionDeportiva::ESTADO_INSCRITO,
                'observaciones_ide'  => $observaciones,
                'use_id'             => Auth::id(),
                'status_id'          => 1,
            ];

            if ($existente) {
                if ($existente->trashed()) {
                    $existente->restore();
                }

                $existente->fill($datos)->save();
                $inscripcion = $existente;
            } else {
                $inscripcion = InscripcionDeportiva::create($datos + [
                    'hde_id' => $bloqueado->id_hde,
                    'per_id' => $persona->id_per,
                ]);
            }

            Bitacora::registrar(
                accion: 'inscripcion_deportiva',
                modulo: 'deporte',
                tablaAfectada: 'inscripcion_deportiva',
                registroId: $inscripcion->id_ide,
                datosModificados: [
                    'hde_id'     => $bloqueado->id_hde,
                    'cld_id'     => $actividad->id_cld,
                    'per_id'     => $persona->id_per,
                    'estado_ide' => InscripcionDeportiva::ESTADO_INSCRITO,
                ],
                observacion: 'Inscripción en "' . $actividad->nombre_cld . '" (' . ($bloqueado->actividad?->nombre_acd ?? 'bloque #' . $bloqueado->id_hde) . ')',
            );

            return $inscripcion;
        });
    }

    public static function retirar(InscripcionDeportiva $inscripcion): void
    {
        $anterior = $inscripcion->estado_ide;

        $inscripcion->estado_ide = InscripcionDeportiva::ESTADO_RETIRADO;
        $inscripcion->save();

        Bitacora::registrar(
            accion: 'retiro_inscripcion_deportiva',
            modulo: 'deporte',
            tablaAfectada: 'inscripcion_deportiva',
            registroId: $inscripcion->id_ide,
            datosAnteriores: ['estado_ide' => $anterior],
            datosModificados: ['estado_ide' => InscripcionDeportiva::ESTADO_RETIRADO],
        );
    }

    public static function nombre(Persona $persona): string
    {
        return trim($persona->nombres_per . ' ' . $persona->apellidos_per);
    }
}
