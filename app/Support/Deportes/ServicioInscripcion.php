<?php

namespace App\Support\Deportes;

use App\Models\Deporte\DeporteHorario;
use App\Models\Deporte\InscripcionDeportiva;
use App\Models\Deporte\TorneoDeportivo;
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

        return Persona::query()
            ->whereNull('deleted_at')
            ->where('cedula_per', $normalizada)
            ->first();
    }

    public static function personaPorCedula(?string $cedula, string $campo = 'cedula'): Persona
    {
        if (Cedula::normalizar($cedula) === null) {
            throw ValidationException::withMessages([$campo => 'La cédula indicada no tiene un formato válido.']);
        }

        $persona = static::buscarPersona($cedula);

        if (! $persona) {
            throw ValidationException::withMessages([$campo => 'No existe ninguna persona registrada con la cédula ' . Cedula::normalizar($cedula) . '.']);
        }

        if ((int) $persona->status_id !== 1) {
            throw ValidationException::withMessages([$campo => 'La persona con esa cédula está inactiva en el sistema.']);
        }

        return $persona;
    }

    public static function inscribirEnTorneo(TorneoDeportivo $torneo, ?string $cedula, ?string $observaciones = null): InscripcionDeportiva
    {
        if (! $torneo->admiteInscripciones()) {
            throw ValidationException::withMessages(['tde_id' => 'El torneo "' . $torneo->nombre_tde . '" no está abierto a inscripciones.']);
        }

        $horario = DeporteHorario::query()->todasLasSedes()->find($torneo->hde_id);

        if (! $horario) {
            throw ValidationException::withMessages(['tde_id' => 'El torneo no tiene un horario válido.']);
        }

        return static::inscribir($horario, $cedula, $observaciones);
    }

    public static function inscribir(DeporteHorario $horario, ?string $cedula, ?string $observaciones = null): InscripcionDeportiva
    {
        $persona = static::personaPorCedula($cedula);

        return DB::transaction(function () use ($horario, $persona, $observaciones) {
            $bloqueado = DeporteHorario::query()->todasLasSedes()->whereKey($horario->getKey())->lockForUpdate()->first();

            if (! $bloqueado || $bloqueado->estado_hde !== DeporteHorario::ESTADO_ACTIVO) {
                throw ValidationException::withMessages(['hde_id' => 'El horario no está activo; no admite inscripciones.']);
            }

            $torneoCerrado = TorneoDeportivo::query()->todasLasSedes()
                ->where('hde_id', $bloqueado->id_hde)
                ->where('estado_tde', '!=', TorneoDeportivo::ESTADO_ABIERTO)
                ->exists();

            $torneoAbierto = TorneoDeportivo::query()->todasLasSedes()
                ->where('hde_id', $bloqueado->id_hde)
                ->where('estado_tde', TorneoDeportivo::ESTADO_ABIERTO)
                ->exists();

            if ($torneoCerrado && ! $torneoAbierto) {
                throw ValidationException::withMessages(['hde_id' => 'El torneo de este horario ya no admite inscripciones.']);
            }

            $existente = InscripcionDeportiva::query()->todasLasSedes()->withTrashed()
                ->where('hde_id', $bloqueado->id_hde)
                ->where('per_id', $persona->id_per)
                ->first();

            if ($existente && ! $existente->trashed() && $existente->estaVigente()) {
                throw ValidationException::withMessages(['cedula' => 'La persona ' . static::nombre($persona) . ' ya está inscrita en este horario.']);
            }

            $ocupados = InscripcionDeportiva::query()->todasLasSedes()
                ->where('hde_id', $bloqueado->id_hde)
                ->where('estado_ide', '!=', InscripcionDeportiva::ESTADO_RETIRADO)
                ->count();

            if ($bloqueado->cupo_maximo_hde !== null && $ocupados >= $bloqueado->cupo_maximo_hde) {
                throw ValidationException::withMessages(['cedula' => 'No hay cupos disponibles: el horario ya tiene ' . $ocupados . ' de ' . $bloqueado->cupo_maximo_hde . ' inscritos.']);
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
                    'per_id'     => $persona->id_per,
                    'cedula_per' => $persona->cedula_per,
                    'estado_ide' => InscripcionDeportiva::ESTADO_INSCRITO,
                ],
                observacion: 'Inscripción de ' . static::nombre($persona) . ' en el horario #' . $bloqueado->id_hde,
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
