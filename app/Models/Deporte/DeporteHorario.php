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

    public const ESTADOS_CON_EVENTOS = [self::ESTADO_ACTIVO, self::ESTADO_COMPLETADO];

    protected $relacionSede = 'calendario';

    protected $table = 'horario_deportivo';

    protected $primaryKey = 'id_hde';

    protected $fillable = [
        'dia_semana_hde',
        'fecha_especifica_hde',
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

    public function horaInicio(string $formato = 'H:i'): string
    {
        return Carbon::parse($this->hora_inicio_hde)->format($formato);
    }

    public function horaFin(string $formato = 'H:i'): string
    {
        return Carbon::parse($this->hora_fin_hde)->format($formato);
    }

    public function horas(): string
    {
        return $this->horaInicio('g:i A') . ' a ' . $this->horaFin('g:i A');
    }

    public function esDeTorneo(): bool
    {
        return (bool) $this->calendario?->esTorneo();
    }

    public function estaActivo(): bool
    {
        return $this->estado_hde === self::ESTADO_ACTIVO;
    }

    public function fechasEntre($desde, $hasta): array
    {
        return $this->calendario ? $this->calendario->fechasEntre($desde, $hasta) : [];
    }

    public function aplicaEnFecha($fecha): bool
    {
        return (bool) $this->calendario?->aplicaEnFecha($fecha);
    }

    public function descripcionCuando(): string
    {
        return trim(($this->calendario?->descripcionCuando() ?? '') . ', ' . $this->horas(), ', ');
    }

    public function etiqueta(): string
    {
        return implode(' · ', array_filter([
            $this->calendario?->nombre_cld,
            $this->actividad?->nombre_acd,
            $this->espacio?->nombre_epd,
            $this->horas(),
        ]));
    }

    public function cuposOcupados(): int
    {
        return $this->inscripcionesVigentes()->count();
    }

    public function cuposDisponibles(): ?int
    {
        return $this->cupo_maximo_hde === null ? null : max(0, $this->cupo_maximo_hde - $this->cuposOcupados());
    }

    public static function seSolapan(string $inicioA, string $finA, string $inicioB, string $finB): bool
    {
        $normalizar = fn ($hora) => Carbon::parse($hora)->format('H:i:s');

        return $normalizar($inicioA) < $normalizar($finB) && $normalizar($inicioB) < $normalizar($finA);
    }

    public static function buscarChoque(int $espacioId, array $fechas, string $horaInicio, string $horaFin, array $excluir = []): ?array
    {
        $fechas = array_values(array_unique($fechas));

        if ($fechas === []) {
            return null;
        }

        sort($fechas);

        $inicio = Carbon::parse($horaInicio)->format('H:i:s');
        $fin = Carbon::parse($horaFin)->format('H:i:s');
        $primera = $fechas[0];
        $ultima = $fechas[count($fechas) - 1];

        $candidatos = static::query()
            ->todasLasSedes()
            ->with(['calendario' => fn ($q) => $q->todasLasSedes(), 'actividad'])
            ->where('epd_id', $espacioId)
            ->where('estado_hde', self::ESTADO_ACTIVO)
            ->when($excluir !== [], fn ($q) => $q->whereNotIn('id_hde', $excluir))
            ->where('hora_inicio_hde', '<', $fin)
            ->where('hora_fin_hde', '>', $inicio)
            ->whereHas('calendario', fn ($q) => $q->todasLasSedes()
                ->where('status_id', 1)
                ->where('fecha_inicio_cld', '<=', $ultima)
                ->where('fecha_fin_cld', '>=', $primera))
            ->orderBy('id_hde')
            ->get();

        foreach ($candidatos as $candidato) {
            $comunes = array_values(array_intersect($fechas, $candidato->fechasEntre($primera, $ultima)));

            if ($comunes !== []) {
                return ['bloque' => $candidato, 'fecha' => $comunes[0]];
            }
        }

        return null;
    }

    public static function mensajeChoque(array $choque, ?string $espacio = null): string
    {
        $bloque = $choque['bloque'];
        $fecha = Carbon::parse($choque['fecha']);

        return sprintf(
            '%s ya está ocupado el %s %s de %s por "%s" (%s). Elige otro espacio u otra hora.',
            $espacio ?? 'El espacio',
            mb_strtolower(DeporteCalendario::DIAS_LEGIBLES[DeporteCalendario::diaDeFecha($fecha)]),
            $fecha->format('d/m/Y'),
            $bloque->horas(),
            $bloque->calendario?->nombre_cld ?? 'otra actividad',
            $bloque->actividad?->nombre_acd ?? 'otra disciplina'
        );
    }
}
