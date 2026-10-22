<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const DIAS = [1 => 'lunes', 2 => 'martes', 3 => 'miercoles', 4 => 'jueves', 5 => 'viernes', 6 => 'sabado', 7 => 'domingo'];

    public function up(): void
    {
        Schema::table('calendario_deportivo', function (Blueprint $table) {
            $table->string('tipo_cld', 20)->default('esporadica');
            $table->string('dias_semana_cld', 80)->nullable();
            $table->string('seccion_cld', 50)->nullable();
            $table->boolean('con_inscripcion_cld')->default(true);
            $table->foreignId('prg_id')->nullable()->constrained('programa', 'id_prg')->nullOnDelete()->cascadeOnUpdate();

            $table->index(['tipo_cld', 'fecha_inicio_cld', 'fecha_fin_cld'], 'calendario_deportivo_tipo_fechas_idx');
        });

        $this->reconstruirAsistencia();
        $this->separarTorneos();
        $this->convertirHorarios();

        Schema::table('horario_deportivo', function (Blueprint $table) {
            $table->dropIndex(['semana_inicio_hde']);
        });

        Schema::table('horario_deportivo', function (Blueprint $table) {
            $table->dropColumn('semana_inicio_hde');
        });
    }

    public function down(): void
    {
        Schema::table('horario_deportivo', function (Blueprint $table) {
            $table->date('semana_inicio_hde')->nullable()->index();
        });

        Schema::dropIfExists('asistencia_deportiva');

        Schema::create('asistencia_deportiva', function (Blueprint $table) {
            $table->id('id_asd');
            $table->foreignId('sad_id')->constrained('sesion_asistencia_deportiva', 'id_sad')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('ide_id')->constrained('inscripcion_deportiva', 'id_ide')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('estado_asd', 20)->default('presente');
            $table->timestamps();
            $table->auditoria();

            $table->unique(['sad_id', 'ide_id']);
        });

        Schema::table('calendario_deportivo', function (Blueprint $table) {
            $table->dropForeign(['prg_id']);
            $table->dropIndex('calendario_deportivo_tipo_fechas_idx');
            $table->dropColumn(['tipo_cld', 'dias_semana_cld', 'seccion_cld', 'con_inscripcion_cld', 'prg_id']);
        });
    }

    private function reconstruirAsistencia(): void
    {
        $anteriores = DB::table('asistencia_deportiva as a')
            ->join('inscripcion_deportiva as i', 'i.id_ide', '=', 'a.ide_id')
            ->orderBy('a.id_asd')
            ->get(['a.sad_id', 'a.ide_id', 'a.estado_asd', 'a.status_id', 'a.created_at', 'a.updated_at', 'a.deleted_at', 'a.created_id', 'a.updated_id', 'i.per_id']);

        Schema::drop('asistencia_deportiva');

        Schema::create('asistencia_deportiva', function (Blueprint $table) {
            $table->id('id_asd');
            $table->foreignId('sad_id')->constrained('sesion_asistencia_deportiva', 'id_sad')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('per_id')->constrained('persona', 'id_per')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('ide_id')->nullable()->constrained('inscripcion_deportiva', 'id_ide')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('ied_id')->nullable()->constrained('integrante_equipo_deporte', 'id_ied')->nullOnDelete()->cascadeOnUpdate();
            $table->string('estado_asd', 20)->default('presente');
            $table->timestamps();
            $table->auditoria();

            $table->unique(['sad_id', 'per_id']);
        });

        $vistos = [];

        foreach ($anteriores as $fila) {
            $clave = $fila->sad_id . '|' . $fila->per_id;

            if (isset($vistos[$clave])) {
                continue;
            }

            $vistos[$clave] = true;

            DB::table('asistencia_deportiva')->insert([
                'sad_id'     => $fila->sad_id,
                'per_id'     => $fila->per_id,
                'ide_id'     => $fila->ide_id,
                'ied_id'     => null,
                'estado_asd' => $fila->estado_asd,
                'status_id'  => $fila->status_id ?? 1,
                'created_at' => $fila->created_at,
                'updated_at' => $fila->updated_at,
                'deleted_at' => $fila->deleted_at,
                'created_id' => $fila->created_id,
                'updated_id' => $fila->updated_id,
            ]);
        }
    }

    private function separarTorneos(): void
    {
        $torneos = DB::table('torneo_deportivo')->whereNull('deleted_at')->orderBy('id_tde')->get();

        foreach ($torneos as $torneo) {
            $horario = DB::table('horario_deportivo')->where('id_hde', $torneo->hde_id)->first();
            $calendario = $horario ? DB::table('calendario_deportivo')->where('id_cld', $horario->cld_id)->first() : null;

            if (! $horario || ! $calendario) {
                continue;
            }

            [$desde, $hasta] = $this->vigenciaLegada($horario, $calendario);
            $dia = $horario->fecha_especifica_hde ? null : $this->normalizarDia($horario->dia_semana_hde);
            $vigente = in_array($torneo->estado_tde, ['abierto', 'en_curso'], true);

            $nuevoCalendario = DB::table('calendario_deportivo')->insertGetId([
                'nombre_cld'          => $torneo->nombre_tde,
                'descripcion_cld'     => $torneo->descripcion_tde,
                'fecha_inicio_cld'    => $desde->toDateString(),
                'fecha_fin_cld'       => $hasta->toDateString(),
                'tipo_cld'            => 'torneo',
                'dias_semana_cld'     => $dia,
                'con_inscripcion_cld' => false,
                'sed_id'              => $calendario->sed_id,
                'use_id'              => $torneo->use_id,
                'status_id'           => 1,
                'created_at'          => now(),
                'updated_at'          => now(),
            ], 'id_cld');

            $fechaUnica = $desde->equalTo($hasta) ? $desde->toDateString() : null;

            if ($calendario->tipo_cld === 'torneo') {
                $bloque = DB::table('horario_deportivo')->insertGetId([
                    'dia_semana_hde'       => null,
                    'fecha_especifica_hde' => $fechaUnica,
                    'semana_inicio_hde'    => null,
                    'hora_inicio_hde'      => $horario->hora_inicio_hde,
                    'hora_fin_hde'         => $horario->hora_fin_hde,
                    'cupo_maximo_hde'      => null,
                    'estado_hde'           => $vigente ? 'activo' : 'completado',
                    'cld_id'               => $nuevoCalendario,
                    'acd_id'               => $horario->acd_id,
                    'epd_id'               => $horario->epd_id,
                    'use_id'               => $horario->use_id,
                    'status_id'            => 1,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ], 'id_hde');

                DB::table('torneo_deportivo')->where('id_tde', $torneo->id_tde)->update(['hde_id' => $bloque, 'updated_at' => now()]);

                continue;
            }

            DB::table('horario_deportivo')->where('id_hde', $horario->id_hde)->update([
                'cld_id'               => $nuevoCalendario,
                'dia_semana_hde'       => null,
                'fecha_especifica_hde' => $fechaUnica,
                'cupo_maximo_hde'      => null,
                'estado_hde'           => $horario->estado_hde === 'activo' && ! $vigente ? 'completado' : $horario->estado_hde,
                'updated_at'           => now(),
            ]);

            $this->inscritosAEquipo($torneo, (int) $horario->id_hde);
        }
    }

    private function inscritosAEquipo(object $torneo, int $horarioId): void
    {
        $inscritos = DB::table('inscripcion_deportiva')
            ->where('hde_id', $horarioId)
            ->whereNull('deleted_at')
            ->where('estado_ide', '!=', 'retirado')
            ->pluck('per_id')
            ->map(fn ($id) => (int) $id)
            ->unique();

        $integrantes = fn () => DB::table('integrante_equipo_deporte as i')
            ->join('equipo_torneo_deportivo as e', 'e.id_etd', '=', 'i.etd_id')
            ->where('e.tde_id', $torneo->id_tde)
            ->whereNull('i.deleted_at')
            ->whereNull('e.deleted_at')
            ->pluck('i.id_ied', 'i.per_id');

        $sinEquipo = $inscritos->diff($integrantes()->keys()->map(fn ($id) => (int) $id));

        if ($sinEquipo->isNotEmpty()) {
            $equipo = DB::table('equipo_torneo_deportivo')->insertGetId([
                'nombre_equipo_etd' => 'Participantes sin equipo',
                'estado_etd'        => 'inscrito',
                'tde_id'            => $torneo->id_tde,
                'prg_id'            => null,
                'status_id'         => 1,
                'created_at'        => now(),
                'updated_at'        => now(),
            ], 'id_etd');

            foreach ($sinEquipo as $personaId) {
                DB::table('integrante_equipo_deporte')->insert([
                    'etd_id'     => $equipo,
                    'per_id'     => $personaId,
                    'status_id'  => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $porPersona = $integrantes();
        $sesiones = DB::table('sesion_asistencia_deportiva')->where('hde_id', $horarioId)->pluck('id_sad');

        foreach (DB::table('asistencia_deportiva')->whereIn('sad_id', $sesiones->all() ?: [0])->get(['id_asd', 'per_id']) as $fila) {
            DB::table('asistencia_deportiva')->where('id_asd', $fila->id_asd)->update([
                'ied_id' => $porPersona[$fila->per_id] ?? null,
                'ide_id' => null,
            ]);
        }

        DB::table('inscripcion_deportiva')->where('hde_id', $horarioId)->whereNull('deleted_at')->update([
            'estado_ide'        => 'retirado',
            'observaciones_ide' => 'Inscripción directa al torneo convertida en integrante de equipo.',
            'deleted_at'        => now(),
            'updated_at'        => now(),
        ]);
    }

    private function convertirHorarios(): void
    {
        $disciplinas = DB::table('actividad_deportiva')->pluck('nombre_acd', 'id_acd');
        $calendarios = DB::table('calendario_deportivo')->where('tipo_cld', '!=', 'torneo')->whereNull('deleted_at')->orderBy('id_cld')->get();

        foreach ($calendarios as $calendario) {
            $horarios = DB::table('horario_deportivo')->where('cld_id', $calendario->id_cld)->whereNull('deleted_at')->orderBy('id_hde')->get();

            if ($horarios->isEmpty()) {
                DB::table('calendario_deportivo')->where('id_cld', $calendario->id_cld)->update([
                    'status_id'  => 2,
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);

                continue;
            }

            $grupos = [];

            foreach ($horarios as $horario) {
                $clave = $horario->fecha_especifica_hde
                    ? 'f|' . Carbon::parse($horario->fecha_especifica_hde)->toDateString()
                    : 'r|' . implode('|', [$horario->acd_id, $horario->epd_id, $horario->hora_inicio_hde, $horario->hora_fin_hde, $horario->cupo_maximo_hde ?? '', $horario->estado_hde]);

                $grupos[$clave][] = $horario;
            }

            $reutilizar = true;

            foreach ($grupos as $clave => $lista) {
                if (str_starts_with($clave, 'f|')) {
                    $fecha = Carbon::parse(substr($clave, 2))->startOfDay();
                    $cabecera = [
                        'nombre_cld'       => $calendario->nombre_cld . ' - ' . $fecha->format('d/m/Y'),
                        'tipo_cld'         => 'esporadica',
                        'dias_semana_cld'  => null,
                        'fecha_inicio_cld' => $fecha->toDateString(),
                        'fecha_fin_cld'    => $fecha->toDateString(),
                    ];
                    $bloques = $lista;
                    $fusionar = [];
                    $fechaBloque = $fecha->toDateString();
                    $sinFechas = false;
                } else {
                    $dias = [];
                    $desde = null;

                    foreach ($lista as $horario) {
                        if ($dia = $this->normalizarDia($horario->dia_semana_hde)) {
                            $dias[$dia] = $dia;
                        }

                        [$inicio] = $this->vigenciaLegada($horario, $calendario);
                        $desde = $desde === null || $inicio->lt($desde) ? $inicio : $desde;
                    }

                    $hasta = Carbon::parse($calendario->fecha_fin_cld)->startOfDay();
                    $dias = array_values(array_filter(self::DIAS, fn ($dia) => isset($dias[$dia])));
                    $fechas = $this->fechasConDias($desde, $hasta, $dias);
                    $nombre = ($disciplinas[$lista[0]->acd_id] ?? 'Actividad') . ' - ' . $calendario->nombre_cld;
                    $sinFechas = $fechas === [];

                    if (count($fechas) <= 1) {
                        $fechaBloque = $fechas[0] ?? $desde->toDateString();
                        $cabecera = [
                            'nombre_cld'       => $nombre,
                            'tipo_cld'         => 'esporadica',
                            'dias_semana_cld'  => null,
                            'fecha_inicio_cld' => $fechaBloque,
                            'fecha_fin_cld'    => $fechaBloque,
                        ];
                    } else {
                        $fechaBloque = null;
                        $cabecera = [
                            'nombre_cld'       => $nombre,
                            'tipo_cld'         => 'recurrente',
                            'dias_semana_cld'  => implode(',', $dias),
                            'fecha_inicio_cld' => $fechas[0],
                            'fecha_fin_cld'    => $hasta->toDateString(),
                        ];
                    }

                    $bloques = [$lista[0]];
                    $fusionar = array_slice($lista, 1);
                }

                $cabecera['nombre_cld'] = mb_substr($cabecera['nombre_cld'], 0, 255);
                $cabecera['updated_at'] = now();

                if ($reutilizar) {
                    DB::table('calendario_deportivo')->where('id_cld', $calendario->id_cld)->update($cabecera);
                    $destino = (int) $calendario->id_cld;
                    $reutilizar = false;
                } else {
                    $destino = DB::table('calendario_deportivo')->insertGetId($cabecera + [
                        'descripcion_cld'     => $calendario->descripcion_cld,
                        'con_inscripcion_cld' => true,
                        'sed_id'              => $calendario->sed_id,
                        'use_id'              => $calendario->use_id,
                        'status_id'           => $calendario->status_id ?? 1,
                        'created_at'          => now(),
                    ], 'id_cld');
                }

                foreach ($bloques as $bloque) {
                    $cambios = [
                        'cld_id'               => $destino,
                        'dia_semana_hde'       => null,
                        'fecha_especifica_hde' => $fechaBloque,
                        'updated_at'           => now(),
                    ];

                    if ($sinFechas && $bloque->estado_hde === 'activo') {
                        $cambios['estado_hde'] = 'cancelado';
                        $cambios['motivo_cancelacion_hde'] = 'Sin fechas dentro del período original al reorganizar las actividades.';
                    }

                    DB::table('horario_deportivo')->where('id_hde', $bloque->id_hde)->update($cambios);
                }

                foreach ($fusionar as $horario) {
                    $this->fusionarHorario((int) $horario->id_hde, (int) $bloques[0]->id_hde);
                }
            }
        }
    }

    private function fusionarHorario(int $origen, int $destino): void
    {
        foreach (DB::table('inscripcion_deportiva')->where('hde_id', $origen)->get() as $inscripcion) {
            $existente = DB::table('inscripcion_deportiva')->where('hde_id', $destino)->where('per_id', $inscripcion->per_id)->first();

            if ($existente) {
                DB::table('asistencia_deportiva')->where('ide_id', $inscripcion->id_ide)->update(['ide_id' => $existente->id_ide]);
                DB::table('inscripcion_deportiva')->where('id_ide', $inscripcion->id_ide)->delete();
            } else {
                DB::table('inscripcion_deportiva')->where('id_ide', $inscripcion->id_ide)->update(['hde_id' => $destino]);
            }
        }

        foreach (DB::table('sesion_asistencia_deportiva')->where('hde_id', $origen)->get() as $sesion) {
            $existente = DB::table('sesion_asistencia_deportiva')->where('hde_id', $destino)->where('fecha_sad', $sesion->fecha_sad)->first();

            if (! $existente) {
                DB::table('sesion_asistencia_deportiva')->where('id_sad', $sesion->id_sad)->update(['hde_id' => $destino]);

                continue;
            }

            $presentes = DB::table('asistencia_deportiva')->where('sad_id', $existente->id_sad)->pluck('per_id')->map(fn ($id) => (int) $id)->all();

            foreach (DB::table('asistencia_deportiva')->where('sad_id', $sesion->id_sad)->get(['id_asd', 'per_id']) as $fila) {
                if (in_array((int) $fila->per_id, $presentes, true)) {
                    DB::table('asistencia_deportiva')->where('id_asd', $fila->id_asd)->delete();
                } else {
                    DB::table('asistencia_deportiva')->where('id_asd', $fila->id_asd)->update(['sad_id' => $existente->id_sad]);
                }
            }

            DB::table('sesion_asistencia_deportiva')->where('id_sad', $sesion->id_sad)->delete();
        }

        DB::table('torneo_deportivo')->where('hde_id', $origen)->update(['hde_id' => $destino]);

        DB::table('horario_deportivo')->where('id_hde', $origen)->update([
            'estado_hde'             => 'cancelado',
            'motivo_cancelacion_hde' => 'Unificado en el bloque #' . $destino . ' al reorganizar las actividades.',
            'deleted_at'             => now(),
            'updated_at'             => now(),
        ]);
    }

    private function vigenciaLegada(object $horario, object $calendario): array
    {
        if ($horario->fecha_especifica_hde) {
            $fecha = Carbon::parse($horario->fecha_especifica_hde)->startOfDay();

            return [$fecha, $fecha->copy()];
        }

        $desde = Carbon::parse($calendario->fecha_inicio_cld)->startOfDay();
        $hasta = Carbon::parse($calendario->fecha_fin_cld)->startOfDay();

        if (! empty($horario->semana_inicio_hde)) {
            $semana = Carbon::parse($horario->semana_inicio_hde)->startOfWeek(Carbon::MONDAY)->startOfDay();

            if ($semana->gt($desde)) {
                $desde = $semana;
            }
        }

        return [$desde, $hasta->lt($desde) ? $desde->copy() : $hasta];
    }

    private function fechasConDias(Carbon $desde, Carbon $hasta, array $dias): array
    {
        $fechas = [];

        for ($dia = $desde->copy(); $dia->lte($hasta); $dia->addDay()) {
            if (in_array(self::DIAS[$dia->dayOfWeekIso], $dias, true)) {
                $fechas[] = $dia->toDateString();
            }
        }

        return $fechas;
    }

    private function normalizarDia(?string $dia): ?string
    {
        if ($dia === null || trim($dia) === '') {
            return null;
        }

        $dia = strtr(mb_strtolower(trim($dia), 'UTF-8'), ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u']);

        return in_array($dia, self::DIAS, true) ? $dia : null;
    }
};
