<?php

namespace App\Models\salud;

use App\Models\Usuario;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class Cita extends Model
{
    use RegistraAutoria;
    use SoftDeletes;

    protected $table = 'cita_psicologica';
    protected $primaryKey = 'id_cps';

    protected $fillable = [
        'paciente_use_id',
        'psicologo_use_id',
        'fecha_cps',
        'hora_cps',
        'estado_cita_cps',
        'cancelado_por_cps',
        'prioridad_texto_cps',
        'motivo_cps',
        'notas_cps',
        'bloque_propuesto_cps',
        'bloques_sugeridos_cps',
        'bloques_propuestos_cps',
        'propuesta_estado_cps',
        'propuesta_bloque_seleccionado_cps',
        'motivo_rechazo_propuesta_cps',
        'esa_id',
        'confirmado_en_cps',
        'status_id',
    ];

    protected $casts = [
        'fecha_cps' => 'date',
        'confirmado_en_cps' => 'datetime',
    ];

    public function paciente()
    {
        return $this->belongsTo(Usuario::class, 'paciente_use_id', 'id_use');
    }

    public function psicologo()
    {
        return $this->belongsTo(Usuario::class, 'psicologo_use_id', 'id_use');
    }


    public function scopePorPsicologo(Builder $query, $psicologoId): Builder
    {
        return $query->when($psicologoId, fn ($q) => $q->where('psicologo_use_id', $psicologoId));
    }

    public function scopePorPaciente(Builder $query, $pacienteId): Builder
    {
        return $query->where('paciente_use_id', $pacienteId);
    }

    public function scopePorEstado(Builder $query, $estado): Builder
    {
        return $query->when($estado, fn($q) => $q->where('estado_cita_cps', $estado));
    }

    public function scopePendientes(Builder $query): Builder
    {
        return $query->where('estado_cita_cps', 'pendiente');
    }

    public function scopeConfirmadas(Builder $query): Builder
    {
        return $query->where('estado_cita_cps', 'confirmada');
    }

    public function scopeRealizadas(Builder $query): Builder
    {
        return $query->where('estado_cita_cps', 'realizada');
    }

    public static function obtenerCitasGlobales($estado = null, $cantidad = 10, $psicologoId = null)
    {
        $paginator = self::with(['paciente.persona', 'psicologo.persona'])
            ->where('psicologo_use_id', $psicologoId)
            ->porEstado($estado)
            ->latest('created_at')
            ->paginate($cantidad);

        $paginator->getCollection()->transform(function ($item) {
            $item->paciente_nombre = $item->paciente && $item->paciente->persona
                ? trim("{$item->paciente->persona->nombres_per} {$item->paciente->persona->apellidos_per}")
                : '';
            $item->psicologo_nombre = $item->psicologo && $item->psicologo->persona
                ? trim("{$item->psicologo->persona->nombres_per} {$item->psicologo->persona->apellidos_per}")
                : '';
            $item->fecha_cps = $item->fecha_cps ? Carbon::parse($item->fecha_cps) : null;
            $item->created_at = $item->created_at ? Carbon::parse($item->created_at) : null;
            return self::desencriptarItem($item);
        });

        return $paginator;
    }

    public static function obtenerEstadisticas($psicologoId, $fechaInicio, $fechaFin, $estado = null, $avanceId = null, $estadoAnimoId = null, $prioridad = null, $perfilAcademico = null, $pnf = null, bool $todosLosPsicologos = false)
    {
        $query = self::with('paciente');

        if (!$todosLosPsicologos) {
            $query->where('psicologo_use_id', $psicologoId);
        }

        if ($fechaInicio && $fechaFin) {
            $query->where(function ($q) use ($fechaInicio, $fechaFin) {
                $q->whereBetween('fecha_cps', [$fechaInicio, $fechaFin])
                    ->orWhereBetween('created_at', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
            });
        }

        $query->porEstado($estado)
            ->when($estadoAnimoId, fn($q) => $q->where('esa_id', $estadoAnimoId))
            ->when($prioridad, fn($q) => $q->where('prioridad_texto_cps', $prioridad))
            ->when($perfilAcademico || $pnf, function ($q) use ($perfilAcademico, $pnf) {
                $q->whereHas('paciente', function ($u) use ($perfilAcademico, $pnf) {
                    if ($perfilAcademico) {
                        $u->whereHas('persona.perfil', function ($p) use ($perfilAcademico) {
                            $p->where('nombre_perfil', $perfilAcademico);
                        });
                    }

                    if ($pnf) {
                        $u->whereHas('persona.personaPnf', function ($pp) use ($pnf) {
                            $pp->whereHas('pnf', function ($p) use ($pnf) {
                                $p->where('nombre_prg', $pnf);
                            });
                        });
                    }
                });
            });

        $citas = $query->latest('created_at')->get();
        $citas->transform(fn($item) => self::desencriptarItem($item));

        return $citas->map(function ($cita) {
            $paciente = $cita->paciente;
            if ($paciente) {
                $cita->nombres = $paciente->persona->nombres_per;
                $cita->apellidos = $paciente->persona->apellidos_per;
                $cita->genero = $paciente->persona->genero_per;
                $cita->fecha_nacimiento = $paciente->persona->fecha_nacimiento_per;
                $cita->perfil_academico = $paciente->persona->perfil->nombre_perfil ?? null;
                $cita->pnf = 'No especificado';

                $personaPnf = $cita->paciente->persona->personaPnf;

                if ($personaPnf instanceof \Illuminate\Support\Collection) {
                    $personaPnf = $personaPnf->first();
                }

                if ($personaPnf && $personaPnf->pnf) {
                    $cita->pnf = $personaPnf->pnf->nombre_prg ?? 'No especificado';
                }

                try {
                    if (strlen($cita->nombres) > 40) $cita->nombres = Crypt::decryptString($cita->nombres);
                } catch (\Exception $e) {
                }
                try {
                    if (strlen($cita->apellidos) > 40) $cita->apellidos = Crypt::decryptString($cita->apellidos);
                } catch (\Exception $e) {
                }
                try {
                    if (strlen($cita->genero) > 40) $cita->genero = Crypt::decryptString($cita->genero);
                } catch (\Exception $e) {
                }
            }

            $cita->paciente_nombre = trim(($cita->paciente->persona->nombres_per ?? '') . ' ' . ($cita->paciente->persona->apellidos_per ?? ''));
            $cita->cita_id = $cita->id_cps;
            $cita->fecha_carbon = $cita->fecha_cps ? Carbon::parse($cita->fecha_cps) : null;
            $cita->created_at_carbon = $cita->created_at ? Carbon::parse($cita->created_at) : null;
            return $cita;
        });
    }

    public static function obtenerResumenEstadistico($citas, $fechaInicio = null, $fechaFin = null, $psicologoId = null)
    {
        $resumen = [
            'total_citas' => $citas->count(),
            'total_pacientes' => 0,
            'genero' => ['masculino' => 0, 'femenino' => 0, 'otro' => 0],
            'edades' => [
                'rangos' => ['0-17' => 0, '18-25' => 0, '26-35' => 0, '36-50' => 0, '51+' => 0],
                'promedio' => 0,
            ],
            'perfil_academico' => [
                'Estudiante' => 0,
                'Profesor' => 0,
                'Obrero' => 0,
                'Administrativo' => 0,
                'Pre-escolar' => 0,
                'Otros' => 0,
                'No especificado' => 0,
            ],
            'pnf' => [
                'ADMINISTRACION' => 0,
                'MECANICA' => 0,
                'MANTENIMIENTO' => 0,
                'ELECTRICIDAD' => 0,
                'VETERINARIA' => 0,
                'INFORMATICA' => 0,
                'PROC. Y DIST. DE ALIMENTOS' => 0,
                'DISTRIBUCIÓN LOGÍSTICA' => 0,
                'AGROALIMENTACION' => 0,
                'SEGURIDAD ALIMENTARIA' => 0,
                'No especificado' => 0,
                'No aplica' => 0,
            ],
            'avances' => [],
            'avances_pacientes' => [],
            'prioridades' => [],
            'prioridades_pacientes' => [],
            'estados_animo' => [],
            'estados_animo_pacientes' => []
        ];

        $avancesDb = DB::table('avance_sesion')->pluck('nombre_avs', 'id_avs')->toArray();
        foreach ($avancesDb as $nombre) {
            $resumen['avances'][$nombre] = 0;
            $resumen['avances_pacientes'][$nombre] = [];
        }
        $resumen['avances']['No especificado'] = 0;
        $resumen['avances_pacientes']['No especificado'] = [];

        $prioridadesDb = DB::table('prioridad_atencion')->pluck('nombre_pat')->toArray();
        foreach ($prioridadesDb as $nombre) {
            $nombreFormat = ucfirst($nombre);
            $resumen['prioridades'][$nombreFormat] = 0;
            $resumen['prioridades_pacientes'][$nombreFormat] = [];
        }
        $resumen['prioridades']['No especificado'] = 0;
        $resumen['prioridades_pacientes']['No especificado'] = [];

        $estadosAnimoDb = DB::table('estado_animo')->pluck('nombre_esa', 'id_esa')->toArray();
        foreach ($estadosAnimoDb as $nombre) {
            $resumen['estados_animo'][$nombre] = 0;
            $resumen['estados_animo_pacientes'][$nombre] = [];
        }
        $resumen['estados_animo']['No especificado'] = 0;
        $resumen['estados_animo_pacientes']['No especificado'] = [];

        $pacientes = [];
        $edadesList = [];
        $horasBloques = [];
        $totalRealizadas = 0;
        $totalEspera = 0;
        $citasConEspera = 0;
        $citasSemanales = [];

        $citasOrdenadas = $citas->sortByDesc('fecha_carbon');

        foreach ($citasOrdenadas as $cita) {
            if ($cita->estado_cita_cps === 'realizada') $totalRealizadas++;

            if ($cita->fecha_carbon && !in_array($cita->estado_cita_cps, ['cancelada', 'rechazada'])) {
                $semanaKey = $cita->fecha_carbon->format('W-Y');
                $citasSemanales[$semanaKey] = ($citasSemanales[$semanaKey] ?? 0) + 1;
            }

            if ($cita->hora_cps) {
                $horaCarbon = Carbon::parse($cita->hora_cps);
                $bloque = $horaCarbon->format('h:00 A') . ' - ' . $horaCarbon->copy()->addHour()->format('h:00 A');
                $horasBloques[$bloque] = ($horasBloques[$bloque] ?? 0) + 1;
            }

            if ($cita->created_at_carbon && $cita->fecha_carbon) {
                $diffDays = $cita->created_at_carbon->startOfDay()->diffInDays($cita->fecha_carbon->copy()->startOfDay());
                $totalEspera += max(0, $diffDays);
                $citasConEspera++;
            }

            if (!isset($pacientes[$cita->paciente_use_id])) {
                $pacientes[$cita->paciente_use_id] = true;
                $resumen['total_pacientes']++;

                $genero = strtolower(trim($cita->paciente->persona->genero_per ?? ''));
                if (in_array($genero, ['masculino', 'hombre', 'm'])) {
                    $resumen['genero']['masculino']++;
                } elseif (in_array($genero, ['femenino', 'mujer', 'f'])) {
                    $resumen['genero']['femenino']++;
                } else {
                    $resumen['genero']['otro']++;
                }

                if ($cita->paciente->persona->fecha_nacimiento_per) {
                    $edad = Carbon::parse($cita->paciente->persona->fecha_nacimiento_per)->age;
                    $edadesList[] = $edad;

                    if ($edad <= 17) $resumen['edades']['rangos']['0-17']++;
                    elseif ($edad <= 25) $resumen['edades']['rangos']['18-25']++;
                    elseif ($edad <= 35) $resumen['edades']['rangos']['26-35']++;
                    elseif ($edad <= 50) $resumen['edades']['rangos']['36-50']++;
                    else $resumen['edades']['rangos']['51+']++;
                }

                $perfil = $cita->paciente->persona->perfil->nombre_perfil ?? 'No especificado';
                if (!in_array($perfil, ['Estudiante', 'Profesor', 'Obrero', 'Administrativo', 'Pre-escolar', 'Otros'])) {
                    $perfil = 'No especificado';
                }
                $resumen['perfil_academico'][$perfil]++;

                $pnfVal = 'No especificado';
                $personaPnf = $cita->paciente->persona->personaPnf;

                if ($personaPnf instanceof \Illuminate\Support\Collection) {
                    $personaPnf = $personaPnf->first();
                }

                if ($personaPnf && $personaPnf->pnf) {
                    $pnfVal = $personaPnf->pnf->nombre_prg ?? 'No especificado';
                }

                if ($pnfVal === 'Agroalimentaria') $pnfVal = 'Agroalimentacion';
                if ($pnfVal === 'Electrica') $pnfVal = 'Electricidad';

                if (!in_array($pnfVal, [
                    'ADMINISTRACION',
                    'MECANICA',
                    'MANTENIMIENTO',
                    'ELECTRICIDAD',
                    'VETERINARIA',
                    'INFORMATICA',
                    'PROC. Y DIST. DE ALIMENTOS',
                    'DISTRIBUCIÓN LOGÍSTICA',
                    'AGROALIMENTACION',
                    'SEGURIDAD ALIMENTARIA'
                ])) {
                    $pnfVal = ($perfil === 'Estudiante') ? 'No especificado' : 'No aplica';
                }
                $resumen['pnf'][$pnfVal] = ($resumen['pnf'][$pnfVal] ?? 0) + 1;

                $avanceId = null;
                if ($cita->notas_cps) {
                    $notas = is_string($cita->notas_cps) ? json_decode($cita->notas_cps, true) : $cita->notas_cps;
                    if (json_last_error() === JSON_ERROR_NONE && is_array($notas) && isset($notas['avance_estado'])) {
                        $avanceId = $notas['avance_estado'];
                    }
                }

                $keyAvance = ($avanceId && isset($avancesDb[$avanceId])) ? $avancesDb[$avanceId] : 'No especificado';
                $resumen['avances'][$keyAvance]++;
                if (!in_array($cita->paciente->persona->nombres_per, $resumen['avances_pacientes'][$keyAvance])) {
                    $resumen['avances_pacientes'][$keyAvance][] = $cita->paciente->persona->nombres_per;
                }

                $prioridad = ucfirst($cita->prioridad_texto_cps ?? 'No especificado');
                if (!isset($resumen['prioridades'][$prioridad])) $prioridad = 'No especificado';
                $resumen['prioridades'][$prioridad]++;
                if (!in_array($cita->paciente->persona->nombres_per, $resumen['prioridades_pacientes'][$prioridad])) {
                    $resumen['prioridades_pacientes'][$prioridad][] = $cita->paciente->persona->nombres_per;
                }

                $estadoAnimoId = $cita->esa_id;
                $keyAnimo = ($estadoAnimoId && isset($estadosAnimoDb[$estadoAnimoId])) ? $estadosAnimoDb[$estadoAnimoId] : 'No especificado';
                $resumen['estados_animo'][$keyAnimo]++;
                if (!in_array($cita->paciente->persona->nombres_per, $resumen['estados_animo_pacientes'][$keyAnimo])) {
                    $resumen['estados_animo_pacientes'][$keyAnimo][] = $cita->paciente->persona->nombres_per;
                }
            }
        }

        if (count($edadesList) > 0) {
            $resumen['edades']['promedio'] = round(array_sum($edadesList) / count($edadesList), 1);
            $sortedEdades = $edadesList;
            sort($sortedEdades);
            $count = count($sortedEdades);
            $middle = intdiv($count - 1, 2);
            $resumen['edades']['mediana'] = ($count % 2 == 0) ? ($sortedEdades[$middle] + $sortedEdades[$middle + 1]) / 2 : $sortedEdades[$middle];
            $counts = array_count_values($edadesList);
            arsort($counts);
            $resumen['edades']['moda'] = array_key_first($counts);
        } else {
            $resumen['edades']['mediana'] = 0;
            $resumen['edades']['moda'] = 0;
        }

        arsort($horasBloques);
        $resumen['hora_pico'] = !empty($horasBloques) ? array_key_first($horasBloques) : 'N/A';
        $resumen['distribucion_horas'] = $horasBloques;

        $semanasTotal = 1;
        if ($fechaInicio && $fechaFin) {
            $inicio = Carbon::parse($fechaInicio);
            $fin = Carbon::parse($fechaFin);
            $dias = $inicio->diffInDays($fin);
            $semanasTotal = max(1, $dias) / 7;
        }
        $resumen['promedio_semanal'] = round($resumen['total_citas'] / max(0.1, $semanasTotal), 1);

        ksort($citasSemanales);
        $resumen['flujo_semanal'] = $citasSemanales;
        $resumen['tasa_asistencia'] = $resumen['total_citas'] > 0 ? round(($totalRealizadas / $resumen['total_citas']) * 100, 1) : 0;
        $resumen['tiempo_espera_promedio'] = $citasConEspera > 0 ? round($totalEspera / $citasConEspera, 1) : 0;

        $resumen['comparativa_pacientes'] = 0;
        if ($fechaInicio && $fechaFin && $psicologoId) {
            $inicio = Carbon::parse($fechaInicio);
            $fin = Carbon::parse($fechaFin);
            $dias = $inicio->diffInDays($fin);
            $prevFin = $inicio->copy()->subDay();
            $prevInicio = $prevFin->copy()->subDays($dias);

            $prevCitas = self::porPsicologo($psicologoId)
                ->where(function ($q) use ($prevInicio, $prevFin) {
                    $q->whereBetween('fecha_cps', [$prevInicio->toDateString(), $prevFin->toDateString()])
                        ->orWhereBetween('created_at', [$prevInicio->toDateString() . ' 00:00:00', $prevFin->toDateString() . ' 23:59:59']);
                })
                ->count();

            $currentCitas = $resumen['total_citas'];
            if ($prevCitas > 0) {
                $resumen['comparativa_pacientes'] = round((($currentCitas - $prevCitas) / $prevCitas) * 100, 1);
            } else if ($currentCitas > 0) {
                $resumen['comparativa_pacientes'] = 100;
            }
        }

        return $resumen;
    }

    public static function instanciarParaNotificacion($id)
    {
        $cita = self::find($id);
        return $cita ? self::desencriptarItem($cita) : null;
    }

    public static function notificarUsuario($userId, $notification)
    {
        $user = Usuario::find($userId);
        if (!$user) return;
        $notifiable = new \App\Models\salud\NotifiableUser();
        $notifiable->id = $user->id_use;
        $notifiable->email = $user->persona->correo_per ?? $user->email_use;
        $notifiable->nombres = $user->persona->nombres_per ?? '';
        $notifiable->apellidos = $user->persona->apellidos_per ?? '';
        $notifiable->name = trim($notifiable->nombres . ' ' . $notifiable->apellidos);
        $notifiable->notify($notification);
    }

    public static function obtenerCitasPorPsicologo($psicologoId, $estado = null, $cantidad = 10)
    {
        $paginator = self::with(['paciente.persona'])
            ->porPsicologo($psicologoId)
            ->porEstado($estado)
            ->latest('created_at')
            ->paginate($cantidad);

        $coleccionFiltrada = $paginator->getCollection()->transform(function ($item) {
            $item->fecha_cps = $item->fecha_cps ? Carbon::parse($item->fecha_cps) : null;
            $item->created_at = $item->created_at ? Carbon::parse($item->created_at) : null;
            $item->paciente_nombre = $item->paciente && $item->paciente->persona
                ? trim("{$item->paciente->persona->nombres_per} {$item->paciente->persona->apellidos_per}")
                : '';
            return self::desencriptarItem($item);
        })->filter(fn($item) => trim($item->motivo_cps) !== 'Nota de Evolución (Manual)')->values();

        $paginator->setCollection($coleccionFiltrada);

        return $paginator;
    }

    public static function obtenerPaciente($userId)
    {
        return Usuario::find($userId);
    }

    public static function obtenerPsicologo($psicologoId)
    {
        return Usuario::find($psicologoId);
    }

    public static function obtenerNotasLimpias($raw)
    {
        if (!$raw) return '';

        if (str_starts_with($raw, '{')) {
            try {
                $data = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                    return $data['observaciones'] ?? ($data['motivo_consulta'] ?? ($data['intervenciones'] ?? 'Sesión con datos estructurados.'));
                }
            } catch (\Exception $e) {
            }
        }

        return $raw;
    }

    public static function obtenerDetalle($id)
    {
        $cita = self::with(['paciente.persona', 'psicologo.persona'])->find($id);
        if (!$cita) return null;

        $cita = self::desencriptarItem($cita);

        if (isset($cita->fecha_cps) && $cita->fecha_cps) $cita->fecha_cps = Carbon::parse($cita->fecha_cps);
        if (isset($cita->created_at) && $cita->created_at) $cita->created_at = Carbon::parse($cita->created_at);
        if (isset($cita->updated_at) && $cita->updated_at) $cita->updated_at = Carbon::parse($cita->updated_at);
        if (isset($cita->confirmado_en_cps) && $cita->confirmado_en_cps) $cita->confirmado_en_cps = Carbon::parse($cita->confirmado_en_cps);

        $pNombre = explode(' ', trim($cita->paciente->persona->nombres_per ?? ''))[0];
        $pApellido = explode(' ', trim($cita->paciente->persona->apellidos_per ?? ''))[0];
        $cita->paciente_short_name = trim($pNombre . ' ' . $pApellido) ?: 'Paciente';

        $psNombre = explode(' ', trim($cita->psicologo->persona->nombres_per ?? ''))[0];
        $psApellido = explode(' ', trim($cita->psicologo->persona->apellidos_per ?? ''))[0];
        $cita->psicologo_short_name = trim($psNombre . ' ' . $psApellido) ?: 'Psicólogo';

        return $cita;
    }

    private static function desencriptarItem($item)
    {
        if (!$item) return $item;

        $campos = ['motivo_cps', 'notas_cps', 'bloque_propuesto_cps', 'bloques_sugeridos_cps', 'bloques_propuestos_cps', 'propuesta_bloque_seleccionado_cps'];
        foreach ($campos as $campo) {
            if (isset($item->$campo) && !empty($item->$campo) && is_string($item->$campo)) {
                if (strlen($item->$campo) > 40 && !str_contains($item->$campo, ' ')) {
                    try {
                        $decrypted = decrypt($item->$campo);
                        $item->$campo = (is_array($decrypted) || is_object($decrypted)) ? json_encode($decrypted) : (string)$decrypted;
                    } catch (\Exception $e) {
                        try {
                            $item->$campo = Crypt::decryptString($item->$campo);
                        } catch (\Exception $e2) {
                        }
                    }
                }
            }
        }
        return $item;
    }

    public static function normalizarBloque($bloque)
    {
        $value = trim($bloque ?? '');
        $value = preg_replace('/[\x{00a0}\x{200b}]+/u', ' ', $value);
        $dias = ['lunes', 'martes', 'miércoles', 'miercoles', 'jueves', 'viernes', 'sábado', 'sabado', 'domingo'];
        $value = str_ireplace($dias, '', $value);

        $value = preg_replace_callback('/(\d{1,2}):(\d{2})\s*(am|pm)\b/i', function ($matches) {
            $hours = (int)$matches[1];
            $ampm = strtolower($matches[3]);
            if ($ampm === 'pm' && $hours < 12) $hours += 12;
            if ($ampm === 'am' && $hours === 12) $hours = 0;
            return sprintf('%02d:%s', $hours, $matches[2]);
        }, $value);

        $value = preg_replace(['/\s*[-–—]\s*/u', '/(\d{1,2}:\d{2}):\d{2}/', '/\s+/'], ['-', '$1', ' '], $value);
        $value = preg_replace('/(^|\s|-|\|)(\d):/', '${1}0$2:', $value);

        return mb_strtolower(str_replace(' ', '', $value), 'UTF-8');
    }

    public static function evaluarPrioridadBasePaciente($userId)
    {
        $paciente = Usuario::find($userId);
        $resetAt = $paciente ? $paciente->infracciones_reset_at_use : null;

        $queryCancelaciones = self::porPaciente($userId)
            ->where('estado_cita_cps', 'cancelada')
            ->where('cancelado_por_cps', 'paciente');

        if ($resetAt) $queryCancelaciones->where('updated_at', '>', $resetAt);
        $cancelacionesPaciente = $queryCancelaciones->count();

        $queryNoAsistencias = self::porPaciente($userId)->where('estado_cita_cps', 'no_asistio');
        if ($resetAt) $queryNoAsistencias->where('updated_at', '>', $resetAt);
        $noAsistencias = $queryNoAsistencias->count();

        return (($cancelacionesPaciente + $noAsistencias) >= 3) ? 'baja' : 'media';
    }

    public static function verificarUmbralInfraccionesPaciente($userId, $psicologoId = null)
    {
        $resetAt = Usuario::where('id_use', $userId)->value('infracciones_reset_at_use');

        $queryCancelaciones = self::porPaciente($userId)
            ->where('estado_cita_cps', 'cancelada')
            ->where('cancelado_por_cps', 'paciente');
        if ($resetAt) $queryCancelaciones->where('updated_at', '>', $resetAt);
        $cancelacionesPaciente = $queryCancelaciones->count();

        $queryNoAsistencias = self::porPaciente($userId)->where('estado_cita_cps', 'no_asistio');
        if ($resetAt) $queryNoAsistencias->where('updated_at', '>', $resetAt);
        $noAsistencias = $queryNoAsistencias->count();

        if (($cancelacionesPaciente + $noAsistencias) == 3) {
            self::notificarUsuario($userId, new \App\Notifications\PenalizacionPacienteNotification());

            if ($psicologoId) {
                self::notificarUsuario($psicologoId, new \App\Notifications\PenalizacionPsicologoNotification((object)['id' => $userId]));
            } else {
                $ultimaCita = self::porPaciente($userId)->latest('created_at')->first();
                if ($ultimaCita) {
                    self::notificarUsuario($ultimaCita->psicologo_use_id, new \App\Notifications\PenalizacionPsicologoNotification((object)['id' => $userId]));
                }
            }
        }
    }

    public static function aplicarRecalculoPrioridad($userId, $psicologoId = null)
    {
        DB::transaction(function () use ($userId) {
            $prioridad = self::evaluarPrioridadBasePaciente($userId);

            $ids = self::porPaciente($userId)
                ->pendientes()
                ->limit(10)
                ->pluck('id_cps');

            self::whereIn('id_cps', $ids)->update(['prioridad_texto_cps' => $prioridad]);
        });
    }

    public static function evaluarAvisoAtencionPsicologo($userId, $psicologoId)
    {
        if (!Usuario::where('id_use', $userId)->exists() || !Usuario::where('id_use', $psicologoId)->exists()) {
            return;
        }

        $rechazos = self::porPaciente($userId)->porPsicologo($psicologoId)->where('estado_cita_cps', 'rechazada')->count();
        $cancelaciones = self::porPaciente($userId)->porPsicologo($psicologoId)->where('estado_cita_cps', 'cancelada')->where('cancelado_por_cps', 'psicologo')->count();

        if (($rechazos + $cancelaciones) == 3) {
            $ultimaCita = self::porPaciente($userId)->porPsicologo($psicologoId)->latest('updated_at')->first();
            if ($ultimaCita) {
                $citaModel = self::instanciarParaNotificacion($ultimaCita->id_cps);
                if ($citaModel) {
                    try {
                        self::notificarUsuario($psicologoId, new \App\Notifications\AvisoAtencionPsicologoNotification((object)['id' => $userId], $citaModel));
                    } catch (\Throwable $e) {
                        report($e);
                    }
                }
            }
        }
    }

    public static function crear($user, $validated)
    {
        return DB::transaction(function () use ($user, $validated) {
            $tieneCitaPendiente = self::porPaciente($user->id_use)
                ->whereIn('estado_cita_cps', ['pendiente', 'confirmada'])
                ->lockForUpdate()
                ->exists();

            if ($tieneCitaPendiente) {
                return [false, 'Tienes una cita pendiente o confirmada. Espera a que se marque como realizada, no asistió o cancelada antes de solicitar otra.', null];
            }

            if (empty($validated['bloques_sugeridos_cps'])) {
                return [false, 'Debes seleccionar un bloque de horario.', null];
            }

            $prioridad = !empty($validated['prioridad']) ? $validated['prioridad'] : self::evaluarPrioridadBasePaciente($user->id_use);

            $psicologo = Usuario::find($validated['psicologo_id']);
            if (!$psicologo || !$psicologo->esPsicologo()) {
                return [false, 'Selecciona un psicólogo válido.', null];
            }

            $cita = self::create([
                'paciente_use_id' => $user->id_use,
                'psicologo_use_id' => $psicologo->id_use,
                'fecha_cps' => $validated['fecha_solicitada'],
                'hora_cps' => null,
                'estado_cita_cps' => 'pendiente',
                'prioridad_texto_cps' => $prioridad,
                'motivo_cps' => Crypt::encryptString($validated['motivo']),
                'bloques_sugeridos_cps' => !empty($validated['bloques_sugeridos_cps']) ? $validated['bloques_sugeridos_cps'] : null,
            ]);

            $citaModel = self::instanciarParaNotificacion($cita->id_cps);
            if ($citaModel) {
                self::notificarUsuario($psicologo->id_use, new \App\Notifications\CitaRequestedNotification($citaModel));
            }

            return [true, 'Solicitud de cita creada correctamente.', $cita];
        });
    }

    public static function confirmar($citaId, $psicologoId, $validated)
    {
        try {
            return DB::transaction(function () use ($citaId, $validated) {
                $cita = self::find($citaId);
                if (!$cita || $cita->estado_cita_cps !== 'pendiente') {
                    return [false, 'Error: Solo se pueden aceptar citas que estén en estado pendiente.'];
                }

                $motivo = '';
                if (!empty($cita->motivo_cps)) {
                    try {
                        $motivo = Crypt::decryptString($cita->motivo_cps);
                    } catch (\Exception $e) {
                        $motivo = $cita->motivo_cps;
                    }
                }

                $isManual = in_array($motivo, ['Asignado manualmente por psicólogo', 'Gestionada por psicólogo'])
                    || str_contains($motivo, 'anualmente')
                    || str_contains($motivo, 'estionada');

                if (!$isManual) {
                    $fechaHoraCita = Carbon::parse($validated['fecha'] . ' ' . $validated['hora']);
                    if ($fechaHoraCita->isBefore(now())) {
                        return [false, 'Validación fallida: No se pueden agendar citas en fechas u horas que ya pasaron.'];
                    }
                }

                $bloqueConfirmadoExistente = self::porPsicologo($cita->psicologo_use_id)
                    ->confirmadas()
                    ->where('fecha_cps', $validated['fecha'])
                    ->where('id_cps', '!=', $citaId)
                    ->get()
                    ->first(function ($otraCita) use ($validated) {
                        if (!$otraCita->bloque_propuesto_cps) return false;
                        try {
                            $bloqueDecrypted = decrypt($otraCita->bloque_propuesto_cps);
                        } catch (\Exception $e) {
                            try {
                                $bloqueDecrypted = Crypt::decryptString($otraCita->bloque_propuesto_cps);
                            } catch (\Exception $e2) {
                                $bloqueDecrypted = $otraCita->bloque_propuesto_cps;
                            }
                        }
                        if (is_array($bloqueDecrypted) || is_object($bloqueDecrypted)) {
                            $bloqueDecrypted = json_encode($bloqueDecrypted);
                        }
                        return $bloqueDecrypted && self::normalizarBloque($bloqueDecrypted) === self::normalizarBloque($validated['bloque']);
                    });

                if ($bloqueConfirmadoExistente) {
                    return [false, 'Conflicto: Este bloque horario ya tiene una cita confirmada para este psicólogo.'];
                }

                $cita->update([
                    'estado_cita_cps' => 'confirmada',
                    'fecha_cps' => $validated['fecha'],
                    'hora_cps' => $validated['hora'],
                    'bloque_propuesto_cps' => Crypt::encryptString($validated['bloque']),
                    'bloques_propuestos_cps' => null,
                    'confirmado_en_cps' => now(),
                ]);

                $pacienteRow = Usuario::find($cita->paciente_use_id);

                if ($pacienteRow) {
                    $citaModel = self::instanciarParaNotificacion($citaId);
                    if ($citaModel) {
                        $correoPaciente = $pacienteRow->persona->correo_per ?? $pacienteRow->email_use;
                        if (filter_var($correoPaciente, FILTER_VALIDATE_EMAIL)) {
                            try {
                                Mail::to($correoPaciente)->send(new \App\Mail\CitaConfirmada($citaModel));
                            } catch (\Throwable $exception) {
                                report($exception);
                            }
                        }
                        self::notificarUsuario($cita->paciente_use_id, new \App\Notifications\CitaConfirmedNotification($citaModel));
                    }
                }

                return [true, 'Cita confirmada con éxito. El paciente ha sido notificado.'];
            });
        } catch (\Exception $e) {
            return [false, 'Error interno al confirmar la cita: ' . \App\Support\Seguridad\MensajeError::de($e)];
        }
    }

    public static function rechazar($citaId, $motivo)
    {
        try {
            return DB::transaction(function () use ($citaId, $motivo) {
                $cita = self::find($citaId);
                if (!$cita || $cita->estado_cita_cps !== 'pendiente') {
                    return [false, 'Error: Solo se pueden rechazar citas con estado pendiente.'];
                }

                $cita->update([
                    'estado_cita_cps' => 'rechazada',
                    'notas_cps' => Crypt::encryptString($motivo ?: 'Lo siento, no puedo atenderte en los horarios solicitados'),
                ]);

                self::evaluarAvisoAtencionPsicologo($cita->paciente_use_id, $cita->psicologo_use_id);

                $citaModel = self::instanciarParaNotificacion($citaId);
                if ($citaModel) {
                    self::notificarUsuario($cita->paciente_use_id, new \App\Notifications\CitaRechazadaNotification($citaModel));
                }

                return [true, 'La solicitud de cita ha sido rechazada correctamente.'];
            });
        } catch (\Exception $e) {
            return [false, 'Error al procesar el rechazo: ' . \App\Support\Seguridad\MensajeError::de($e)];
        }
    }

    public static function marcarRealizada($citaId, $psicologoId)
    {
        try {
            return DB::transaction(function () use ($citaId) {
                $cita = self::find($citaId);
                if (!$cita || $cita->estado_cita_cps !== 'confirmada') {
                    return [false, 'Error: Solo se pueden marcar como realizada citas que ya han sido confirmadas.'];
                }

                $cita->update(['estado_cita_cps' => 'realizada']);

                return [true, 'La cita ha sido marcada como realizada exitosamente.'];
            });
        } catch (\Exception $e) {
            return [false, 'Error interno al marcar como realizada: ' . \App\Support\Seguridad\MensajeError::de($e)];
        }
    }

    public static function marcarNoAsistio($citaId)
    {
        try {
            return DB::transaction(function () use ($citaId) {
                $cita = self::find($citaId);
                if (!$cita || $cita->estado_cita_cps !== 'confirmada') {
                    return [false, 'Error: Solo se pueden marcar como "no asistió" citas que estaban confirmadas.'];
                }

                $cita->update(['estado_cita_cps' => 'no_asistio']);

                self::verificarUmbralInfraccionesPaciente($cita->paciente_use_id, $cita->psicologo_use_id);
                self::aplicarRecalculoPrioridad($cita->paciente_use_id, $cita->psicologo_use_id);

                return [true, 'Cita marcada como "no asistió". Se han procesado las penalizaciones correspondientes.'];
            });
        } catch (\Exception $e) {
            return [false, 'Error al procesar la inasistencia: ' . \App\Support\Seguridad\MensajeError::de($e)];
        }
    }

    public static function obtenerFechaPrimeraCita($userId, $psicologoId)
    {
        return self::porPaciente($userId)
            ->where('psicologo_use_id', $psicologoId)
            ->whereNotNull('fecha_cps')
            ->orderBy('fecha_cps', 'asc')
            ->value('fecha_cps');
    }

    public static function cancelar($citaId, $userId, $motivo = null)
    {
        try {
            return DB::transaction(function () use ($citaId, $userId, $motivo) {
                $cita = self::find($citaId);
                if (!$cita) {
                    return [false, 'Error: El registro de la cita no existe.'];
                }

                $user = Usuario::find($userId);
                if (!$user) {
                    return [false, 'Error: Usuario no identificado.'];
                }

                $actor = (int) $cita->psicologo_use_id === (int) $user->id_use ? 'psicologo' : 'paciente';

                if ($actor === 'psicologo' || $actor === 'admin') {
                    if ($cita->estado_cita_cps !== 'confirmada') {
                        return [false, 'Validación: Solo se pueden cancelar citas que ya estén confirmadas.'];
                    }

                    $notas = $motivo ?: 'Lo siento, no podré atenderte, surgió un inconveniente a última hora.';

                    $cita->update([
                        'estado_cita_cps' => 'cancelada',
                        'cancelado_por_cps' => 'psicologo',
                        'notas_cps' => Crypt::encryptString($notas),
                    ]);

                    self::aplicarRecalculoPrioridad($cita->paciente_use_id, $cita->psicologo_use_id);
                    self::evaluarAvisoAtencionPsicologo($cita->paciente_use_id, $cita->psicologo_use_id);

                    $citaModel = self::instanciarParaNotificacion($citaId);
                    if ($citaModel) {
                        self::notificarUsuario($cita->paciente_use_id, new \App\Notifications\CitaCancelledNotification($citaModel, 'psicologo'));
                    }
                } else {
                    if (!in_array($cita->estado_cita_cps, ['pendiente', 'confirmada'])) {
                        return [false, 'Validación: Sólo se pueden cancelar citas pendientes o confirmadas.'];
                    }

                    if ($actor === 'paciente' && $cita->estado_cita_cps === 'confirmada' && $cita->fecha_cps && $cita->hora_cps) {
                        $fechaSolo = Carbon::parse($cita->fecha_cps)->format('Y-m-d');
                        $horaInicioStr = $cita->hora_cps;
                        if (preg_match('/(\d{1,2}:\d{2}(?:\s*[aApP][mM])?)/i', $cita->hora_cps, $m)) {
                            $horaInicioStr = $m[1];
                        }
                        $fechaHoraCita = Carbon::parse($fechaSolo . ' ' . $horaInicioStr);

                        if ($fechaHoraCita->isPast()) {
                            return [false, 'No puedes cancelar una cita que ya ha empezado. Queda a la espera del procesamiento del psicólogo.'];
                        }
                    }

                    $cita->update([
                        'estado_cita_cps' => 'cancelada',
                        'cancelado_por_cps' => $actor,
                    ]);

                    if ($actor === 'paciente') {
                        self::verificarUmbralInfraccionesPaciente($cita->paciente_use_id, $cita->psicologo_use_id);
                        self::aplicarRecalculoPrioridad($cita->paciente_use_id, $cita->psicologo_use_id);

                        $citaModel = self::instanciarParaNotificacion($citaId);
                        if ($citaModel) {
                            self::notificarUsuario($cita->psicologo_use_id, new \App\Notifications\CitaCancelledNotification($citaModel, $actor));
                        }
                    }
                }

                return [true, 'La cita ha sido cancelada exitosamente.'];
            });
        } catch (\Exception $e) {
            return [false, 'Error interno al cancelar la cita: ' . \App\Support\Seguridad\MensajeError::de($e)];
        }
    }

    public static function posponer($citaId, $userId)
    {
        try {
            return DB::transaction(function () use ($citaId, $userId) {
                $cita = self::find($citaId);
                if (!$cita) {
                    return [false, 'Error: El registro de la cita no existe.'];
                }

                $user = Usuario::find($userId);
                if (!$user || !$user->tieneRol('psicologo') || $cita->psicologo_use_id !== $user->id_use) {
                    return [false, 'Error: Usuario no autorizado para esta acción.'];
                }

                if ($cita->estado_cita_cps !== 'confirmada') {
                    return [false, 'Validación: Solo se pueden posponer citas que ya estén confirmadas.'];
                }

                if ($cita->fecha_cps && $cita->hora_cps) {
                    $fechaSolo = Carbon::parse($cita->fecha_cps)->format('Y-m-d');
                    $fechaHoraCita = Carbon::parse($fechaSolo . ' ' . $cita->hora_cps);
                    if ($fechaHoraCita->isPast()) {
                        return [false, 'Validación: No es posible posponer una cita cuya hora programada ya ha comenzado o pasado. En su lugar, registre si el paciente asistió o no.'];
                    }
                }

                $cita->update([
                    'estado_cita_cps' => 'pendiente',
                    'bloque_propuesto_cps' => null,
                    'bloques_propuestos_cps' => null,
                    'propuesta_estado_cps' => null,
                    'propuesta_bloque_seleccionado_cps' => null,
                ]);

                $citaModel = self::instanciarParaNotificacion($citaId);
                if ($citaModel) {
                    try {
                        self::notificarUsuario($cita->paciente_use_id, new \App\Notifications\CitaCancelledNotification($citaModel, 'pospuesta'));
                    } catch (\Throwable $e) {
                        report($e);
                    }
                }

                return [true, 'La cita ha sido pospuesta y devuelta a pendientes.'];
            });
        } catch (\Exception $e) {
            return [false, 'Error interno al posponer la cita: ' . \App\Support\Seguridad\MensajeError::de($e)];
        }
    }

    public static function proponer($citaId, $psicologoId, $fecha, $nuevoBloque)
    {
        try {
            return DB::transaction(function () use ($citaId, $psicologoId, $fecha, $nuevoBloque) {
                $cita = self::find($citaId);
                if (!$cita) return [false, 'Error: Cita no encontrada.'];

                $cita = self::desencriptarItem($cita);

                if ($cita->motivo_cps !== 'Gestionada por psicólogo' && !self::validarBloqueFuturo($fecha, $nuevoBloque)) {
                    return [false, 'No se pueden proponer fechas u horas pasadas.'];
                }

                $bloqueConFecha = $fecha . '|' . $nuevoBloque;
                $bloqueNormalizado = self::normalizarBloque($nuevoBloque);

                $bloqueConfirmadoExistente = self::porPsicologo($psicologoId)
                    ->where('fecha_cps', $fecha)
                    ->confirmadas()
                    ->get()
                    ->first(function ($otraCita) use ($bloqueNormalizado) {
                        return $otraCita->bloque_propuesto_cps && self::normalizarBloque($otraCita->bloque_propuesto_cps) === $bloqueNormalizado;
                    });

                if ($bloqueConfirmadoExistente) {
                    return [false, 'Conflicto: Este bloque horario ya tiene una cita confirmada para esta fecha.'];
                }

                $propuestaPendienteExistente = self::porPsicologo($psicologoId)
                    ->where('id_cps', '!=', $citaId)
                    ->where('propuesta_estado_cps', 'pendiente')
                    ->get()
                    ->first(function ($otraCita) use ($bloqueConFecha) {
                        $otraCitaDes = self::desencriptarItem($otraCita);
                        $bloquesOtros = array_filter(array_map('trim', explode(';', $otraCitaDes->bloques_propuestos_cps ?? '')));
                        return in_array($bloqueConFecha, $bloquesOtros);
                    });

                if ($propuestaPendienteExistente) {
                    return [false, 'Conflicto: Ya has enviado una solicitud para esta fecha y bloque a otro paciente que está en espera de respuesta.'];
                }

                $bloquesPropuestos = array_filter(array_map('trim', explode(';', $cita->bloques_propuestos_cps ?? '')));

                if (in_array($bloqueConFecha, $bloquesPropuestos)) {
                    if ($cita->propuesta_estado_cps === 'rechazada') {
                        return [false, 'El paciente ya rechazó una propuesta para este mismo horario. Por favor, elige otro bloque.'];
                    }
                    return [false, 'Este paciente ya se encuentra propuesto en este bloque.'];
                }

                $bloquesPropuestos[] = $bloqueConFecha;
                $cita->update([
                    'bloques_propuestos_cps' => Crypt::encryptString(implode(';', $bloquesPropuestos)),
                ]);

                return [true, 'Bloque propuesto correctamente.'];
            });
        } catch (\Exception $e) {
            return [false, 'Error interno al proponer bloque: ' . \App\Support\Seguridad\MensajeError::de($e)];
        }
    }

    public static function quitarPropuesta($citaId, $fecha, $bloque)
    {
        try {
            return DB::transaction(function () use ($citaId, $fecha, $bloque) {
                $cita = self::find($citaId);
                if (!$cita) {
                    return [false, 'Cita no encontrada.'];
                }

                $cita = self::desencriptarItem($cita);

                if (!$bloque || !$fecha) {
                    return [true, 'No se especificó la fecha o el bloque.'];
                }

                $bloqueConFecha = $fecha . '|' . $bloque;

                $propuestos = array_filter(array_map('trim', explode(';', $cita->bloques_propuestos_cps ?? '')));
                $propuestos = array_filter($propuestos, fn($item) => $item !== $bloqueConFecha);
                $nuevosPropuestos = $propuestos ? implode(';', $propuestos) : null;

                $cita->update([
                    'bloques_propuestos_cps' => $nuevosPropuestos ? Crypt::encryptString($nuevosPropuestos) : null,
                ]);

                return [true, 'Propuesta retirada.'];
            });
        } catch (\Exception $e) {
            return [false, 'Error al quitar propuesta: ' . \App\Support\Seguridad\MensajeError::de($e)];
        }
    }

    public static function obtenerPendientes($psicologoId, $prioridadFilter = null, $q = null, $perPage = 15)
    {
        if (!$psicologoId) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }

        $prioridades = Prioridad::obtenerParaPsicologo($psicologoId);
        $validPrioridades = $prioridades->pluck('nombre_pat')->toArray();
        $orderedPrioridades = $prioridades->sortByDesc('nivel_gravedad_pat')->pluck('nombre_pat')->toArray();

        $confirmedPatientIds = self::porPsicologo($psicologoId)
            ->confirmadas()
            ->pluck('paciente_use_id')
            ->unique()
            ->all();

        $query = self::with('paciente')
            ->porPsicologo($psicologoId)
            ->pendientes();

        if (!empty($confirmedPatientIds)) {
            $query->whereNotIn('paciente_use_id', $confirmedPatientIds);
        }

        if ($prioridadFilter && in_array(strtolower($prioridadFilter), array_map('strtolower', $validPrioridades), true)) {
            $query->where('prioridad_texto_cps', strtolower($prioridadFilter));
        }

        if ($q) {
            $buscarNormalized = mb_strtolower($q, 'UTF-8');
            $query->whereHas('paciente.persona', function ($s) use ($buscarNormalized) {
                $s->whereRaw("LOWER(COALESCE(nombres_per, '')) LIKE ?", ["%{$buscarNormalized}%"])
                    ->orWhereRaw("LOWER(COALESCE(apellidos_per, '')) LIKE ?", ["%{$buscarNormalized}%"])
                    ->orWhereRaw("LOWER(TRIM(CONCAT(COALESCE(nombres_per, ''), ' ', COALESCE(apellidos_per, '')))) LIKE ?", ["%{$buscarNormalized}%"])
                    ->orWhereRaw("LOWER(COALESCE(cedula_per, '')) LIKE ?", ["{$buscarNormalized}%"]);
            });
        }

        $prioridadesList = count($orderedPrioridades) > 0 ? $orderedPrioridades : ['crítica', 'alta', 'media', 'baja'];
        $prioridadesList = array_values($prioridadesList);
        $caseSql = "CASE prioridad_texto_cps ";
        foreach ($prioridadesList as $index => $prio) {
            $caseSql .= "WHEN ? THEN " . ($index + 1) . " ";
        }
        $caseSql .= "ELSE " . (count($prioridadesList) + 1) . " END";

        $paginator = $query->orderByRaw($caseSql, $prioridadesList)
            ->latest('created_at')
            ->paginate($perPage);

        $paginator->getCollection()->transform(function ($item) {
            $item->fecha_cps = $item->fecha_cps ? Carbon::parse($item->fecha_cps) : null;
            $item->created_at = $item->created_at ? Carbon::parse($item->created_at) : null;

            if ($item->paciente) {
                $persona = $item->paciente->persona;
                $item->user_nombres = $persona->nombres_per ?? '';
                $item->user_apellidos = $persona->apellidos_per ?? '';
                $item->paciente_email = $persona->correo_per ?? '';
                $item->paciente_cedula = $persona->cedula_per ?? '';
            }

            $item = self::desencriptarItem($item);

            $firstName = explode(' ', trim($item->user_nombres ?? ''))[0];
            $firstLastName = explode(' ', trim($item->user_apellidos ?? ''))[0];
            $item->paciente_short_name = trim($firstName . ' ' . $firstLastName) ?: 'Paciente';

            return $item;
        });

        return $paginator;
    }

    public static function obtenerHistorialPaciente($userId, $cantidad = 12, $startDate = null, $endDate = null, $prioridad = null)
    {
        $query = self::with('psicologo')
            ->porPaciente($userId)
            ->whereIn('estado_cita_cps', ['realizada', 'no_asistio', 'cancelada', 'rechazada']);

        if ($startDate) $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) $query->whereDate('created_at', '<=', $endDate);
        if ($prioridad) $query->where('prioridad_texto_cps', $prioridad);

        $paginator = $query->latest('created_at')->paginate($cantidad);

        $coleccionFiltrada = $paginator->getCollection()->transform(function ($item) {
            $item->fecha_cps = $item->fecha_cps ? Carbon::parse($item->fecha_cps) : null;
            $item->created_at = $item->created_at ? Carbon::parse($item->created_at) : null;

            if ($item->psicologo) {
                $item->nombres = $item->psicologo->persona->nombres_per;
                $item->apellidos = $item->psicologo->persona->apellidos_per;
            }

            $item = self::desencriptarItem($item);

            $firstName = explode(' ', trim($item->nombres ?? ''))[0];
            $firstLastName = explode(' ', trim($item->apellidos ?? ''))[0];
            $item->psicologo_short_name = trim($firstName . ' ' . $firstLastName);
            $item->psicologo_nombre = $item->psicologo_short_name;

            if (trim($item->motivo_cps) === 'Asignado manualmente por psicólogo') {
                $item->motivo_cps = 'Gestionada por psicólogo';
            }

            return $item;
        })->filter(fn($item) => trim($item->motivo_cps) !== 'Nota de Evolución (Manual)')->values();

        $paginator->setCollection($coleccionFiltrada);

        return $paginator;
    }

    public static function obtenerHistorial($psicologoId, $cantidad = 12, $startDate = null, $endDate = null, $estado = null, $avanceId = null, $estadoAnimoId = null, $prioridad = null, $tipoFiltroFecha = 'rango')
    {
        $query = self::with('paciente')->porPsicologo($psicologoId);

        if (!empty($startDate) || !empty($endDate)) {
            if ($tipoFiltroFecha === 'primera_cita' || $tipoFiltroFecha === 'ultima_cita') {
                $func = ($tipoFiltroFecha === 'primera_cita') ? 'MIN(fecha_cps)' : 'MAX(fecha_cps)';
                $subquery = self::select('paciente_use_id')
                    ->porPsicologo($psicologoId)
                    ->realizadas()
                    ->groupBy('paciente_use_id');

                if (!empty($startDate)) $subquery->havingRaw("{$func} >= ?", [$startDate]);
                if (!empty($endDate)) $subquery->havingRaw("{$func} <= ?", [$endDate]);

                $query->whereIn('paciente_use_id', $subquery->pluck('paciente_use_id'));
            } else {
                if ($startDate) $query->whereDate('created_at', '>=', $startDate);
                if ($endDate) $query->whereDate('created_at', '<=', $endDate);
            }
        }

        if ($estado) {
            if ($estado === 'cancelada_paciente') {
                $query->where('estado_cita_cps', 'cancelada')->where('cancelado_por_cps', 'paciente');
            } elseif ($estado === 'cancelada_psicologo') {
                $query->where('estado_cita_cps', 'cancelada')->where('cancelado_por_cps', 'psicologo');
            } elseif ($estado === 'sin_cita') {
                $query->whereDoesntHave('paciente.citas', function ($q) use ($psicologoId) {
                    $q->where('psicologo_use_id', $psicologoId)->where('estado_cita_cps', 'realizada');
                });
            } else {
                $query->where('estado_cita_cps', $estado);
            }
        }

        if ($estadoAnimoId) $query->where('esa_id', $estadoAnimoId);
        if ($prioridad) $query->where('prioridad_texto_cps', $prioridad);

        $paginator = $query->latest('created_at')->paginate($cantidad);

        $coleccionFiltrada = $paginator->getCollection()->transform(function ($item) {
            $item->fecha_cps = $item->fecha_cps ? Carbon::parse($item->fecha_cps) : null;
            $item->created_at = $item->created_at ? Carbon::parse($item->created_at) : null;

            if ($item->paciente) {
                $item->user_nombres = $item->paciente->persona->nombres_per;
                $item->user_apellidos = $item->paciente->persona->apellidos_per;
            }

            $item = self::desencriptarItem($item);

            $firstName = explode(' ', trim($item->user_nombres ?? ''))[0];
            $firstLastName = explode(' ', trim($item->user_apellidos ?? ''))[0];
            $item->paciente_short_name = trim($firstName . ' ' . $firstLastName) ?: 'Paciente';
            $item->paciente_nombre = trim(($item->user_nombres ?? '') . ' ' . ($item->user_apellidos ?? '')) ?: 'Paciente';

            return $item;
        })->filter(fn($item) => trim($item->motivo_cps) !== 'Nota de Evolución (Manual)')->values();

        $paginator->setCollection($coleccionFiltrada);

        if ($avanceId) {
            $filtered = $paginator->getCollection()->filter(function ($item) use ($avanceId) {
                if (!$item->notas_cps) return false;
                $notes = is_string($item->notas_cps) ? json_decode($item->notas_cps, true) : $item->notas_cps;
                return (json_last_error() === JSON_ERROR_NONE && is_array($notes) && isset($notes['avance_estado']) && $notes['avance_estado'] == $avanceId);
            });
            $paginator->setCollection($filtered->values());
        }

        return $paginator;
    }

    public static function actualizarNota($cita, $notas)
    {
        return DB::transaction(function () use ($cita, $notas) {
            $model = is_object($cita) ? $cita : self::findOrFail($cita);
            return $model->update(['notas_cps' => Crypt::encryptString($notas)]);
        });
    }

    public static function actualizarPrioridad($cita, $prioridad)
    {
        try {
            return DB::transaction(function () use ($cita, $prioridad) {
                $id = is_object($cita) ? $cita->id_cps : $cita;

                // Actualizar directamente en la base de datos para evitar guardar atributos dinámicos como paciente_short_name
                $updated = self::where('id_cps', $id)->update(['prioridad_texto_cps' => $prioridad]);

                if (!$updated) {
                    throw new \Exception('No se pudo guardar la prioridad en la base de datos.');
                }

                return [true, 'Prioridad actualizada correctamente.'];
            });
        } catch (\Exception $e) {
            return [false, 'Error al actualizar prioridad: ' . \App\Support\Seguridad\MensajeError::de($e)];
        }
    }

    public static function obtenerEstadisticasPaciente($pacienteId, $psicologoId)
    {
        $realizadasQuery = self::porPaciente($pacienteId)
            ->porPsicologo($psicologoId)
            ->realizadas()
            ->where('status_id', 1)
            ->get(['id_cps', 'motivo_cps']);

        $realizadasCount = $realizadasQuery->filter(function ($cita) {
            try {
                return Crypt::decryptString($cita->motivo_cps) !== 'Nota de Evolución (Manual)';
            } catch (\Exception $e) {
                return true;
            }
        })->count();

        $inasistenciasCount = self::porPaciente($pacienteId)->porPsicologo($psicologoId)->where('estado_cita_cps', 'no_asistio')->count();
        $pacienteCancelPreCount = self::porPaciente($pacienteId)->porPsicologo($psicologoId)->where('estado_cita_cps', 'cancelada')->where('cancelado_por_cps', 'paciente')->whereNull('confirmado_en_cps')->count();
        $pacienteCancelPostCount = self::porPaciente($pacienteId)->porPsicologo($psicologoId)->where('estado_cita_cps', 'cancelada')->where('cancelado_por_cps', 'paciente')->whereNotNull('confirmado_en_cps')->count();
        $psicologoCancelCount = self::porPaciente($pacienteId)->porPsicologo($psicologoId)->where('estado_cita_cps', 'cancelada')->where('cancelado_por_cps', 'psicologo')->count();
        $rechazadasCount = self::porPaciente($pacienteId)->porPsicologo($psicologoId)->where('estado_cita_cps', 'rechazada')->count();

        return [
            'realizadas' => $realizadasCount,
            'inasistencias' => $inasistenciasCount,
            'paciente_cancel_pre' => $pacienteCancelPreCount,
            'paciente_cancel_post' => $pacienteCancelPostCount,
            'psicologo_cancel' => $psicologoCancelCount,
            'rechazadas' => $rechazadasCount,
            'total' => ($realizadasCount + $inasistenciasCount + $pacienteCancelPreCount + $pacienteCancelPostCount + $psicologoCancelCount + $rechazadasCount),
        ];
    }

    public static function obtenerCitasRealizadas($pacienteId, $psicologoId)
    {
        $paginator = self::porPaciente($pacienteId)
            ->porPsicologo($psicologoId)
            ->realizadas()
            ->where('status_id', 1)
            ->orderBy('fecha_cps', 'desc')
            ->orderBy('hora_cps', 'desc')
            ->paginate(10);

        $paginator->getCollection()->transform(function ($item) use ($pacienteId, $psicologoId) {
            $item->fecha_cps = $item->fecha_cps ? Carbon::parse($item->fecha_cps) : null;
            $item = self::desencriptarItem($item);

            $item->notas_limpias = 'Sin notas registradas.';
            $data = null;
            if ($item->notas_cps) {
                $data = json_decode($item->notas_cps, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                    $preview = null;
                    foreach (['observaciones', 'avance_detalle', 'motivo_consulta', 'intervenciones'] as $previewField) {
                        if (!empty(trim($data[$previewField] ?? ''))) {
                            $preview = $data[$previewField];
                            break;
                        }
                    }
                    $item->notas_limpias = $preview ?: 'Sin observaciones.';
                } else {
                    $item->notas_limpias = $item->notas_cps;
                }
            }

            if ($item->motivo_cps === 'Nota de Evolución (Manual)') {
                $item->is_manual = true;
                $item->display_title = (is_array($data) && !empty($data['titulo_manual'])) ? $data['titulo_manual'] : 'Nota de Evolución';
            } else {
                $item->is_manual = false;
                $item->display_title = $item->motivo_cps ?? 'Consulta General';

                $item->session_number = self::porPaciente($pacienteId)
                    ->porPsicologo($psicologoId)
                    ->realizadas()
                    ->where('status_id', 1)
                    ->where(function ($q) use ($item) {
                        $q->where('fecha_cps', '<', $item->fecha_cps->format('Y-m-d'))
                            ->orWhere(function ($q2) use ($item) {
                                $q2->where('fecha_cps', $item->fecha_cps->format('Y-m-d'))
                                    ->where('hora_cps', '<=', $item->hora_cps);
                            });
                    })
                    ->get(['id_cps', 'motivo_cps'])
                    ->filter(function ($c) {
                        try {
                            return Crypt::decryptString($c->motivo_cps) !== 'Nota de Evolución (Manual)';
                        } catch (\Exception $e) {
                            return true;
                        }
                    })->count();
            }

            return $item;
        });

        return $paginator;
    }

    public static function crearNotaManual($pacienteId, $psicologoId)
    {
        return DB::transaction(function () use ($pacienteId, $psicologoId) {
            return self::create([
                'paciente_use_id' => $pacienteId,
                'psicologo_use_id' => $psicologoId,
                'fecha_cps' => Carbon::today(),
                'hora_cps' => Carbon::now()->format('H:i'),
                'estado_cita_cps' => 'realizada',
                'motivo_cps' => Crypt::encryptString('Nota de Evolución (Manual)'),
                'notas_cps' => Crypt::encryptString(''),
            ]);
        });
    }

    public static function obtenerCitasPorRango($psicologoId, $inicio, $fin)
    {
        return self::with('paciente')
            ->porPsicologo($psicologoId)
            ->whereBetween('fecha_cps', [$inicio, $fin])
            ->orderBy('fecha_cps')
            ->orderBy('hora_cps')
            ->get()
            ->map(function ($item) {
                $item->fecha_cps = $item->fecha_cps ? Carbon::parse($item->fecha_cps) : null;
                if ($item->paciente) {
                    $item->user_nombres = $item->paciente->persona->nombres_per;
                    $item->user_apellidos = $item->paciente->persona->apellidos_per;
                    }

                $item = self::desencriptarItem($item);
                $firstName = explode(' ', trim($item->user_nombres ?? ''))[0];
                $firstLastName = explode(' ', trim($item->user_apellidos ?? ''))[0];
                $item->paciente_short_name = trim($firstName . ' ' . $firstLastName) ?: 'Paciente';
                $item->paciente_nombre = trim(($item->user_nombres ?? '') . ' ' . ($item->user_apellidos ?? '')) ?: 'Paciente';

                return $item;
            });
    }

    public static function actualizar($id, $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $cita = self::findOrFail($id);
            return $cita->update($data);
        });
    }

    public static function eliminar($id)
    {
        return DB::transaction(function () use ($id) {
            $cita = self::findOrFail($id);
            return $cita->update(['estado_cita_cps' => 'cancelada']);
        });
    }

    public static function obtenerCitasAsignadas($psicologoId)
    {
        return self::with('paciente')
            ->porPsicologo($psicologoId)
            ->confirmadas()
            ->get()
            ->map(function ($item) {
                $item->fecha_cps = $item->fecha_cps ? Carbon::parse($item->fecha_cps) : null;
                if ($item->paciente) {
                    $item->user_nombres = $item->paciente->persona->nombres_per;
                    $item->user_apellidos = $item->paciente->persona->apellidos_per;
                }

                $item = self::desencriptarItem($item);
                $firstName = explode(' ', trim($item->user_nombres ?? ''))[0];
                $firstLastName = explode(' ', trim($item->user_apellidos ?? ''))[0];
                $item->paciente_short_name = trim($firstName . ' ' . $firstLastName) ?: 'Paciente';
                $item->paciente_nombre = trim(($item->user_nombres ?? '') . ' ' . ($item->user_apellidos ?? '')) ?: 'Paciente';

                return $item;
            });
    }

    public static function obtenerCitasPorFecha($psicologoId, $fecha)
    {
        return self::with('paciente')
            ->porPsicologo($psicologoId)
            ->whereDate('fecha_cps', $fecha)
            ->get()
            ->map(function ($item) {
                $item->fecha_cps = $item->fecha_cps ? Carbon::parse($item->fecha_cps) : null;
                if ($item->paciente) {
                    $item->user_nombres = $item->paciente->persona->nombres_per;
                    $item->user_apellidos = $item->paciente->persona->apellidos_per;
                }

                $item = self::desencriptarItem($item);
                $firstName = explode(' ', trim($item->user_nombres ?? ''))[0];
                $firstLastName = explode(' ', trim($item->user_apellidos ?? ''))[0];
                $item->paciente_short_name = trim($firstName . ' ' . $firstLastName) ?: 'Paciente';
                $item->paciente_nombre = trim(($item->user_nombres ?? '') . ' ' . ($item->user_apellidos ?? '')) ?: 'Paciente';

                return $item;
            });
    }

    public static function obtenerPorPaciente($userId)
    {
        return self::with('psicologo')
            ->porPaciente($userId)
            ->latest('created_at')
            ->get()
            ->map(function ($item) {
                $item->fecha_cps = $item->fecha_cps ? Carbon::parse($item->fecha_cps) : null;
                $item->created_at = $item->created_at ? Carbon::parse($item->created_at) : null;

                if ($item->psicologo) {
                    $item->psicologo_nombres = $item->psicologo->persona->nombres_per;
                    $item->psicologo_apellidos = $item->psicologo->persona->apellidos_per;
                }

                $item = self::desencriptarItem($item);
                $item->bloques_propuestos_raw = $item->bloques_propuestos_cps;
                $firstName = explode(' ', trim($item->psicologo_nombres ?? ''))[0];
                $firstLastName = explode(' ', trim($item->psicologo_apellidos ?? ''))[0];
                $item->psicologo_nombre = trim($firstName . ' ' . $firstLastName) ?: 'N/A';

                if (trim($item->motivo_cps) === 'Asignado manualmente por psicólogo') {
                    $item->motivo_cps = 'Gestionada por psicólogo';
                }

                return $item;
            })
            ->filter(fn($item) => trim($item->motivo_cps) !== 'Nota de Evolución (Manual)')
            ->values();
    }

    public static function tieneCitaActiva($userId)
    {
        return self::porPaciente($userId)
            ->whereIn('estado_cita_cps', ['pendiente', 'confirmada'])
            ->exists();
    }

    public static function obtenerDataAgenda($request, $user)
    {
        $psicologos = collect();
        $psicologoId = $user->id_use;

        $view = $request->input('view', 'week');
        $dateStr = $request->input('date', now()->toDateString());
        $currentDate = Carbon::parse($dateStr);

        $citasCalendario = collect();
        $calendarioData = [];

        if ($view === 'month') {
            $startOfMonth = $currentDate->copy()->startOfMonth();
            $endOfMonth = $currentDate->copy()->endOfMonth();
            $startOfGrid = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
            $endOfGrid = $endOfMonth->copy()->endOfWeek(Carbon::SATURDAY);

            $citasCalendario = self::obtenerCitasPorRango($psicologoId, $startOfGrid->toDateString(), $endOfGrid->toDateString());

            $tempDate = $startOfGrid->copy();
            while ($tempDate <= $endOfGrid) {
                $calendarioData[] = [
                    'date' => $tempDate->toDateString(),
                    'day' => $tempDate->day,
                    'isCurrentMonth' => $tempDate->month === $currentDate->month,
                    'isToday' => $tempDate->isToday(),
                    'citas' => $citasCalendario->filter(fn($c) => $c->fecha_cps->isSameDay($tempDate))
                ];
                $tempDate->addDay();
            }
        } elseif ($view === 'week') {
            $currentDate = ($currentDate->dayOfWeek === Carbon::SUNDAY)
                ? $currentDate->copy()->next(Carbon::MONDAY)
                : $currentDate->copy()->startOfWeek(Carbon::MONDAY);

            $startOfWeek = $currentDate;
            $endOfWeek = $currentDate->copy()->endOfWeek(Carbon::FRIDAY);
            $citasCalendario = self::obtenerCitasPorRango($psicologoId, $startOfWeek->toDateString(), $endOfWeek->toDateString());
        } elseif ($view === 'list') {
            $startDate = $request->input('start_date', now()->subMonth()->toDateString());
            $endDate = $request->input('end_date', now()->toDateString());
            $estado = $request->input('estado');
            $avanceId = $request->input('avance_id');
            $estadoAnimoId = $request->input('esa_id');
            $prioridadHistorial = $request->input('prioridad');
            $tipoFiltroFecha = $request->input('tipo_filtro_fecha', 'rango');
            $citasCalendario = self::obtenerHistorial($psicologoId, 12, $startDate, $endDate, $estado, $avanceId, $estadoAnimoId, $prioridadHistorial, $tipoFiltroFecha);
        }

        $prioridadFilter = trim((string) $request->input('prioridad'));
        $q = trim((string) $request->input('q'));
        $citasPendientes = self::obtenerPendientes($psicologoId, $prioridadFilter, $q);

        $grupoActivo = $psicologoId ? GrupoHorario::obtenerActivoPorPsicologo($psicologoId) : null;
        $horarios = $grupoActivo ? Horario::obtenerPorGrupo($grupoActivo->id_gha) : collect();

        $citasAsignadas = $psicologoId ? self::obtenerCitasAsignadas($psicologoId) : collect();
        $prioridadesDisponibles = $psicologoId ? Prioridad::obtenerParaPsicologo($psicologoId) : collect();

        return [
            'view' => $view,
            'currentDate' => $currentDate,
            'calendarioData' => $calendarioData,
            'citasCalendario' => $citasCalendario,
            'grupoActivo' => $grupoActivo,
            'horarios' => $horarios,
            'citasPendientes' => $citasPendientes,
            'citasAsignadas' => $citasAsignadas,
            'psicologos' => $psicologos,
            'psicologoId' => $psicologoId,
            'prioridadesDisponibles' => $prioridadesDisponibles
        ];
    }

    public static function obtenerCitasDiariasJson($psicologoId, $fecha)
    {
        return self::obtenerCitasPorFecha($psicologoId, $fecha)->map(fn($c) => [
            'id' => $c->id_cps,
            'paciente' => $c->paciente_nombre ?? 'Paciente sin nombre',
            'hora' => $c->hora_cps ? Carbon::parse($c->hora_cps)->format('g:i A') : 'S/H',
            'estado' => $c->estado_cita_cps,
            'paciente_id' => $c->paciente_use_id
        ]);
    }

    public static function contarCitas()
    {
        return self::count();
    }

    public static function contarCitasHoy()
    {
        return self::whereDate('fecha_cps', Carbon::today())->count();
    }

    public static function obtenerCitasConfirmadasHoyPorPsicologo($psicologoId, $limit = 3)
    {
        return self::with('paciente')
            ->porPsicologo($psicologoId)
            ->confirmadas()
            ->whereDate('fecha_cps', Carbon::today())
            ->whereTime('hora_cps', '>=', Carbon::now()->format('H:i:s'))
            ->orderBy('hora_cps')
            ->take($limit)
            ->get()
            ->map(function ($cita) {
                $cita->paciente_nombre = $cita->paciente ? trim("{$cita->paciente->persona->nombres_per} {$cita->paciente->persona->apellidos_per}") : '';
                return $cita;
            });
    }

    public static function enviarPropuesta($citaId, $psicologoId = null)
    {
        try {
            return DB::transaction(function () use ($citaId, $psicologoId) {
                $cita = self::find($citaId);
                if (!$cita) {
                    return [false, 'Cita no encontrada.'];
                }

                if ($psicologoId && $cita->psicologo_use_id != $psicologoId) {
                    return [false, 'No tienes permiso para esta acción.'];
                }

                if ($cita->propuesta_estado_cps === 'pendiente') {
                    return [false, 'Ya se envió una contrapropuesta y está en espera de respuesta por parte del paciente.'];
                }

                $citaDes = self::desencriptarItem($cita);
                $propuestos = array_filter(array_map('trim', explode(';', $citaDes->bloques_propuestos_cps ?? '')));

                if (empty($propuestos)) {
                    return [false, 'No hay bloques propuestos para enviar. Primero arrastra al paciente a los bloques que deseas proponer.'];
                }

                foreach ($propuestos as $b) {
                    $parts = explode('|', $b);
                    if (count($parts) === 2 && $citaDes->motivo_cps !== 'Gestionada por psicólogo' && !self::validarBloqueFuturo($parts[0], $parts[1])) {
                        return [false, 'Uno de los bloques propuestos ya ha pasado. Por favor, remuévelo antes de enviar la propuesta.'];
                    }
                }

                $cita->update(['propuesta_estado_cps' => 'pendiente']);

                $citaModel = self::instanciarParaNotificacion($citaId);
                if ($citaModel) {
                    self::notificarUsuario($cita->paciente_use_id, new \App\Notifications\ContrapropuestaCitaNotification($citaModel));
                }

                return [true, 'La contrapropuesta ha sido enviada al paciente. Los bloques propuestos: ' . implode(', ', $propuestos)];
            });
        } catch (\Exception $e) {
            return [false, 'Error al enviar la propuesta: ' . \App\Support\Seguridad\MensajeError::de($e)];
        }
    }

    public static function responderPropuesta($citaId, $respuesta, $bloqueSeleccionado = null, $motivoRechazo = null, $nuevosBloques = null)
    {
        try {
            return DB::transaction(function () use ($citaId, $respuesta, $bloqueSeleccionado, $motivoRechazo, $nuevosBloques) {
                $cita = self::find($citaId);
                if (!$cita || $cita->propuesta_estado_cps !== 'pendiente') {
                    return [false, 'No hay una propuesta pendiente para responder.'];
                }

                if ($respuesta === 'aceptada') {
                    $citaDes = self::desencriptarItem($cita);
                    $bloquesPropuestos = array_filter(array_map('trim', explode(';', $citaDes->bloques_propuestos_cps ?? '')));

                    if (empty($bloquesPropuestos)) {
                        return [false, 'No hay bloque propuesto para confirmar.'];
                    }

                    $bloqueAConfirmar = $bloqueSeleccionado ?: $bloquesPropuestos[0];

                    $parts = explode('|', $bloqueAConfirmar);
                    if (count($parts) === 2 && $citaDes->motivo_cps !== 'Gestionada por psicólogo' && !self::validarBloqueFuturo($parts[0], $parts[1])) {
                        return [false, 'Esta propuesta no puede ser seleccionada porque su fecha u hora ya ha pasado.'];
                    }

                    if ($bloqueSeleccionado && !in_array($bloqueSeleccionado, $bloquesPropuestos)) {
                        return [false, 'El bloque seleccionado no es válido.'];
                    }

                    $partes = explode('|', $bloqueAConfirmar, 2);
                    $fecha = $partes[0] ?? null;
                    $bloqueLabel = $partes[1] ?? null;

                    if (!$fecha || !$bloqueLabel) {
                        return [false, 'El formato del bloque propuesto no es válido.'];
                    }

                    preg_match('/(\d{1,2}:\d{2})/', $bloqueLabel, $horaMatch);
                    $hora = $horaMatch[1] ?? '00:00';

                    $cita->update([
                        'propuesta_estado_cps' => 'aceptada',
                        'propuesta_bloque_seleccionado_cps' => Crypt::encryptString($bloqueAConfirmar),
                    ]);

                    $resultado = self::confirmar($citaId, $cita->psicologo_use_id, [
                        'fecha' => $fecha,
                        'hora' => $hora,
                        'bloque' => $bloqueLabel,
                    ]);

                    if ($resultado[0]) {
                        $citaModel = self::instanciarParaNotificacion($citaId);
                        if ($citaModel) {
                            self::notificarUsuario($cita->psicologo_use_id, new \App\Notifications\RespuestaPropuestaNotification($citaModel, 'aceptada'));
                        }
                        return [true, 'Contrapropuesta aceptada con éxito.'];
                    }

                    return $resultado;
                } elseif ($respuesta === 'rechazada') {
                    if ($nuevosBloques) {
                        $citaDes = self::desencriptarItem($cita);
                        $propuestosArr = array_filter(array_map('trim', explode(';', $citaDes->bloques_propuestos_cps ?? '')));

                        $pacienteBloques = [];
                        $parts = explode('|', $nuevosBloques);
                        $horariosPart = '';
                        foreach ($parts as $p) {
                            if (str_contains($p, 'Horarios propuestos:')) {
                                $horariosPart = trim(str_replace('Horarios propuestos:', '', $p));
                                break;
                            }
                        }

                        if ($horariosPart && $horariosPart !== 'Ninguno') {
                            $diasConSlots = array_filter(array_map('trim', explode(';', $horariosPart)));
                            foreach ($diasConSlots as $diaConSlot) {
                                $colonPos = strpos($diaConSlot, ':');
                                if ($colonPos !== false) {
                                    $fecha = trim(substr($diaConSlot, 0, $colonPos));
                                    $slotsStr = trim(substr($diaConSlot, $colonPos + 1));
                                    $slots = array_filter(array_map('trim', explode(',', $slotsStr)));
                                    foreach ($slots as $slot) {
                                        $pacienteBloques[] = $fecha . '|' . $slot;
                                    }
                                }
                            }
                        }

                        $propuestosArrNorm = array_map(fn($pb) => self::normalizarBloque($pb), $propuestosArr);

                        foreach ($pacienteBloques as $pbPac) {
                            if (in_array(self::normalizarBloque($pbPac), $propuestosArrNorm)) {
                                return [false, 'La fecha y hora que sugieres coincide con uno de los bloques de la contrapropuesta enviada por el psicólogo. Si deseas ese horario, por favor acepta la propuesta en lugar de sugerirla nuevamente.'];
                            }
                        }

                        $updateData = [
                            'propuesta_estado_cps' => null,
                            'bloques_propuestos_cps' => null,
                            'bloques_sugeridos_cps' => Crypt::encryptString($nuevosBloques),
                        ];
                    } else {
                        $updateData = ['propuesta_estado_cps' => 'rechazada'];
                    }

                    if ($motivoRechazo) {
                        $updateData['motivo_cps'] = Crypt::encryptString($motivoRechazo);
                    }

                    $cita->update($updateData);

                    $psicologoRow = Usuario::find($cita->psicologo_use_id);
                    if ($psicologoRow) {
                        $citaModel = self::instanciarParaNotificacion($citaId);
                        if ($citaModel) {
                            self::notificarUsuario($cita->psicologo_use_id, new \App\Notifications\ContrapropuestaRechazadaNotification($citaModel));
                        }
                    }

                    return [true, 'Contrapropuesta rechazada y nueva solicitud enviada.'];
                }

                $update = ['propuesta_estado_cps' => $respuesta];
                if ($respuesta === 'sugerencia_aceptada' && $bloqueSeleccionado) {
                    $update['propuesta_bloque_seleccionado_cps'] = Crypt::encryptString($bloqueSeleccionado);
                }

                $cita->update($update);

                $citaModel = self::instanciarParaNotificacion($citaId);
                if ($citaModel && $citaModel->psicologo_use_id) {
                    self::notificarUsuario($citaModel->psicologo_use_id, new \App\Notifications\RespuestaPropuestaNotification($citaModel, 'aceptada'));
                }

                return [true, 'Respuesta registrada correctamente.'];
            });
        } catch (\Exception $e) {
            return [false, 'Error al registrar respuesta: ' . \App\Support\Seguridad\MensajeError::de($e)];
        }
    }

    public static function validarBloqueFuturo($fecha, $bloqueLabel)
    {
        preg_match('/(\d{1,2}:\d{2}\s*(?:AM|PM)?)/i', $bloqueLabel, $horaMatch);
        $hora = $horaMatch[1] ?? '00:00';
        try {
            return !Carbon::parse($fecha . ' ' . $hora)->isPast();
        } catch (\Exception $e) {
            return true;
        }
    }

    public static function ocultarMensajeCancelacion($citaId)
    {
        try {
            return DB::transaction(function () use ($citaId) {
                $cita = self::findOrFail($citaId);
                $cita->update(['bloque_propuesto_cps' => null]);
                return [true, 'Mensaje ocultado correctamente.'];
            });
        } catch (\Exception $e) {
            return [false, 'Error al ocultar el mensaje: ' . \App\Support\Seguridad\MensajeError::de($e)];
        }
    }

    public static function obtenerUltimasCitasConfirmadasPsicologo($psicologoId, $limit = 5)
    {
        return self::with('paciente')
            ->porPsicologo($psicologoId)
            ->confirmadas()
            ->latest('updated_at')
            ->take($limit)
            ->get()
            ->map(function ($cita) {
                $nombres = explode(' ', trim($cita->paciente->persona->nombres_per ?? ''));
                $apellidos = explode(' ', trim($cita->paciente->persona->apellidos_per ?? ''));
                $cita->paciente_nombre = trim("{$cita->paciente->persona->nombres_per} {$cita->paciente->persona->apellidos_per}");
                $cita->paciente_nombre_corto = ($nombres[0] ?? '') . ' ' . ($apellidos[0] ?? '');
                return $cita;
            });
    }

    public static function obtenerEstadisticasCitasPsicologo($psicologoId)
    {
        $stats = self::select('estado_cita_cps', DB::raw('count(*) as total'))
            ->porPsicologo($psicologoId)
            ->whereIn('estado_cita_cps', ['realizada', 'cancelada'])
            ->groupBy('estado_cita_cps')
            ->get()
            ->keyBy('estado_cita_cps');

        return [
            'realizada' => $stats->has('realizada') ? $stats->get('realizada')->total : 0,
            'cancelada' => $stats->has('cancelada') ? $stats->get('cancelada')->total : 0,
        ];
    }

    public static function obtenerTendenciaSemanalCitasPsicologo($psicologoId, $semanas = 4)
    {
        $tendencia = [];
        for ($i = $semanas - 1; $i >= 0; $i--) {
            $inicioSemana = Carbon::now()->subWeeks($i)->startOfWeek();
            $finSemana = Carbon::now()->subWeeks($i)->endOfWeek();

            $total = self::porPsicologo($psicologoId)
                ->realizadas()
                ->whereBetween('fecha_cps', [$inicioSemana->toDateString(), $finSemana->toDateString()])
                ->count();

            $tendencia[] = [
                'semana' => 'Sem ' . $inicioSemana->weekOfYear,
                'total' => $total
            ];
        }
        return collect($tendencia);
    }

    public static function obtenerCitasPendientesAntiguasPsicologo($psicologoId, $limit = 5)
    {
        return self::with('paciente')
            ->porPsicologo($psicologoId)
            ->pendientes()
            ->oldest('created_at')
            ->take($limit)
            ->get()
            ->map(function ($cita) {
                $nombres = explode(' ', trim($cita->paciente->persona->nombres_per ?? ''));
                $apellidos = explode(' ', trim($cita->paciente->persona->apellidos_per ?? ''));
                $cita->paciente_nombre = trim("{$cita->paciente->persona->nombres_per} {$cita->paciente->persona->apellidos_per}");
                $cita->paciente_nombre_corto = ($nombres[0] ?? '') . ' ' . ($apellidos[0] ?? '');
                return $cita;
            });
    }

    public static function obtenerCitasPsicologoPacienteRaw($pacienteId, $psicologoId)
    {
        return self::porPaciente($pacienteId)
            ->porPsicologo($psicologoId)
            ->get();
    }

    public static function eliminarFisicamente($id)
    {
        return self::withTrashed()->where('id_cps', $id)->forceDelete();
    }

    public static function obtenerEstadisticasPorPsicologo($fechaInicio, $fechaFin)
    {
        return self::select('psicologo_use_id', DB::raw('count(*) as total'))
            ->with('psicologo.persona')
            ->where(function ($q) use ($fechaInicio, $fechaFin) {
                $q->whereBetween('fecha_cps', [$fechaInicio, $fechaFin])
                ->orWhereBetween('created_at', [
                    $fechaInicio . ' 00:00:00',
                    $fechaFin . ' 23:59:59',
                ]);
            })
            ->groupBy('psicologo_use_id')
            ->orderByDesc('total')
            ->get()
            ->map(function ($item) {
                $nombre = 'Sin asignar';
                if ($item->psicologo && $item->psicologo->persona) {
                    $nombre = trim(
                        ($item->psicologo->persona->nombres_per ?? '') . ' ' .
                        ($item->psicologo->persona->apellidos_per ?? '')
                    );
                }
                return [
                    'id'     => $item->psicologo_use_id,
                    'nombre' => $nombre ?: 'Sin asignar',
                    'total'  => (int) $item->total,
                ];
            })
            ->values()
            ->all();
    }
}
