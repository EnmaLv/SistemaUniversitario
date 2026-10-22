<?php

namespace App\Models\Deporte;

use App\Models\Usuario;
use App\Traits\PerteneceASedePorRelacion;
use App\Traits\RegistraAutoria;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeporteHorario extends Model
{
    use PerteneceASedePorRelacion;
    use RegistraAutoria;
    use SoftDeletes;

    public const ESTADO_ACTIVO = 'activo';

    public const ESTADO_CANCELADO = 'cancelado';

    public const ESTADO_COMPLETADO = 'completado';

    public const DIAS = [
        1 => 'lunes',
        2 => 'martes',
        3 => 'miercoles',
        4 => 'jueves',
        5 => 'viernes',
        6 => 'sabado',
        7 => 'domingo',
    ];

    public const DIAS_LEGIBLES = [
        'lunes'     => 'Lunes',
        'martes'    => 'Martes',
        'miercoles' => 'Miércoles',
        'jueves'    => 'Jueves',
        'viernes'   => 'Viernes',
        'sabado'    => 'Sábado',
        'domingo'   => 'Domingo',
    ];

    protected $relacionSede = 'calendario';

    protected $table = 'horario_deportivo';

    protected $primaryKey = 'id_hde';

    protected $fillable = [
        'dia_semana_hde',
        'fecha_especifica_hde',
        'semana_inicio_hde',
        'hora_inicio_hde',
        'hora_fin_hde',
        'cupo_maximo_hde',
        'observaciones_hde',
        'estado_hde',
        'motivo_cancelacion_hde',
        'cld_id',
        'acd_id',
        'epd_id',
        'use_id',
        'status_id',
    ];

    protected $casts = [
        'fecha_especifica_hde' => 'date',
        'semana_inicio_hde'    => 'date',
        'cupo_maximo_hde'      => 'integer',
        'status_id'            => 'integer',
    ];

    public function calendario()
    {
        return $this->belongsTo(DeporteCalendario::class, 'cld_id', 'id_cld');
    }

    public function actividad()
    {
        return $this->belongsTo(DeporteActividad::class, 'acd_id', 'id_acd');
    }

    public function espacio()
    {
        return $this->belongsTo(DeporteEspacio::class, 'epd_id', 'id_epd');
    }

    public function responsable()
    {
        return $this->belongsTo(Usuario::class, 'use_id', 'id_use');
    }

    public function inscripciones()
    {
        return $this->hasMany(InscripcionDeportiva::class, 'hde_id', 'id_hde');
    }

    public function inscripcionesVigentes()
    {
        return $this->inscripciones()->where('estado_ide', '!=', InscripcionDeportiva::ESTADO_RETIRADO);
    }

    public function torneos()
    {
        return $this->hasMany(TorneoDeportivo::class, 'hde_id', 'id_hde');
    }

    public function sesiones()
    {
        return $this->hasMany(SesionAsistencia::class, 'hde_id', 'id_hde');
    }

    public function scopeVigentes(Builder $consulta): Builder
    {
        return $consulta->where($this->qualifyColumn('estado_hde'), self::ESTADO_ACTIVO);
    }

    public function scopeEnRango(Builder $consulta, $desde, $hasta): Builder
    {
        $desde = Carbon::parse($desde)->toDateString();
        $hasta = Carbon::parse($hasta)->toDateString();

        return $consulta->where(function (Builder $q) use ($desde, $hasta) {
            $q->whereBetween($this->qualifyColumn('fecha_especifica_hde'), [$desde, $hasta])
                ->orWhere(function (Builder $recurrente) use ($desde, $hasta) {
                    $recurrente->whereNull($this->qualifyColumn('fecha_especifica_hde'))
                        ->where(fn (Builder $s) => $s->whereNull($this->qualifyColumn('semana_inicio_hde'))->orWhereDate($this->qualifyColumn('semana_inicio_hde'), '<=', $hasta))
                        ->whereHas('calendario', fn (Builder $c) => $c->whereDate('fecha_inicio_cld', '<=', $hasta)->whereDate('fecha_fin_cld', '>=', $desde));
                });
        });
    }

    public static function normalizarDia(?string $dia): ?string
    {
        if ($dia === null || trim($dia) === '') {
            return null;
        }

        $dia = strtr(mb_strtolower(trim($dia), 'UTF-8'), ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u']);

        return in_array($dia, self::DIAS, true) ? $dia : null;
    }

    public static function diaDeFecha($fecha): string
    {
        return self::DIAS[Carbon::parse($fecha)->dayOfWeekIso];
    }

    public function esFechaEspecifica(): bool
    {
        return $this->fecha_especifica_hde !== null;
    }

    public function diaClave(): ?string
    {
        return $this->esFechaEspecifica()
            ? self::diaDeFecha($this->fecha_especifica_hde)
            : self::normalizarDia($this->dia_semana_hde);
    }

    public function diaLegible(): string
    {
        return self::DIAS_LEGIBLES[$this->diaClave()] ?? '-';
    }

    public function horaInicio(string $formato = 'H:i'): string
    {
        return Carbon::parse($this->hora_inicio_hde)->format($formato);
    }

    public function horaFin(string $formato = 'H:i'): string
    {
        return Carbon::parse($this->hora_fin_hde)->format($formato);
    }

    public function descripcionCuando(): string
    {
        $horas = $this->horaInicio('g:i A') . ' - ' . $this->horaFin('g:i A');

        return $this->esFechaEspecifica()
            ? $this->diaLegible() . ' ' . $this->fecha_especifica_hde->format('d/m/Y') . ', ' . $horas
            : 'Todos los ' . mb_strtolower($this->diaLegible()) . ', ' . $horas;
    }

    public function etiqueta(): string
    {
        return trim(($this->actividad?->nombre_acd ?? 'Actividad') . ' · ' . ($this->espacio?->nombre_epd ?? 'Sin espacio') . ' · ' . $this->descripcionCuando());
    }

    public static function vigenciaRecurrente($semanaInicio, $inicioCalendario, $finCalendario): array
    {
        $desde = Carbon::parse($inicioCalendario)->startOfDay();

        if ($semanaInicio) {
            $semana = Carbon::parse($semanaInicio)->startOfWeek(Carbon::MONDAY)->startOfDay();

            if ($semana->gt($desde)) {
                $desde = $semana;
            }
        }

        return [$desde, Carbon::parse($finCalendario)->startOfDay()];
    }

    public function vigencia(): ?array
    {
        if ($this->esFechaEspecifica()) {
            $fecha = $this->fecha_especifica_hde->copy()->startOfDay();

            return [$fecha, $fecha->copy()];
        }

        $calendario = $this->calendario;

        if (! $calendario) {
            return null;
        }

        return self::vigenciaRecurrente($this->semana_inicio_hde, $calendario->fecha_inicio_cld, $calendario->fecha_fin_cld);
    }

    public function aplicaEnFecha($fecha): bool
    {
        $fecha = Carbon::parse($fecha)->startOfDay();
        $vigencia = $this->vigencia();

        return $vigencia !== null
            && self::diaDeFecha($fecha) === $this->diaClave()
            && $fecha->betweenIncluded($vigencia[0], $vigencia[1]);
    }

    public function cuposOcupados(): int
    {
        return $this->inscripcionesVigentes()->count();
    }

    public function cuposDisponibles(): ?int
    {
        return $this->cupo_maximo_hde === null ? null : max(0, $this->cupo_maximo_hde - $this->cuposOcupados());
    }

    public static function compartenDia(?string $diaA, Carbon $desdeA, Carbon $hastaA, ?string $diaB, Carbon $desdeB, Carbon $hastaB): bool
    {
        if ($diaA === null || $diaA !== $diaB) {
            return false;
        }

        $desde = $desdeA->gt($desdeB) ? $desdeA->copy() : $desdeB->copy();
        $hasta = $hastaA->lt($hastaB) ? $hastaA->copy() : $hastaB->copy();

        for ($i = 0; $i < 7 && $desde->lte($hasta); $i++, $desde->addDay()) {
            if (self::diaDeFecha($desde) === $diaA) {
                return true;
            }
        }

        return false;
    }

    public static function buscarSolape(int $espacioId, ?string $dia, $desde, $hasta, string $horaInicio, string $horaFin, ?int $excluirId = null): ?self
    {
        $inicio = Carbon::parse($horaInicio)->format('H:i:s');
        $fin = Carbon::parse($horaFin)->format('H:i:s');
        $desde = Carbon::parse($desde)->startOfDay();
        $hasta = Carbon::parse($hasta)->startOfDay();
        $dia = self::normalizarDia($dia);

        $candidatos = static::query()
            ->todasLasSedes()
            ->with(['calendario' => fn ($q) => $q->todasLasSedes(), 'actividad'])
            ->where('epd_id', $espacioId)
            ->where('estado_hde', self::ESTADO_ACTIVO)
            ->when($excluirId, fn ($q) => $q->where('id_hde', '!=', $excluirId))
            ->where('hora_inicio_hde', '<', $fin)
            ->where('hora_fin_hde', '>', $inicio)
            ->get();

        foreach ($candidatos as $candidato) {
            $vigencia = $candidato->vigencia();

            if ($vigencia !== null && self::compartenDia($dia, $desde, $hasta, $candidato->diaClave(), $vigencia[0], $vigencia[1])) {
                return $candidato;
            }
        }

        return null;
    }
}
