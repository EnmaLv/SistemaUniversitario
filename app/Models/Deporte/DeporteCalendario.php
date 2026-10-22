<?php

namespace App\Models\Deporte;

use App\Models\Pnf;
use App\Models\Sede;
use App\Models\Usuario;
use App\Traits\PerteneceASede;
use App\Traits\RegistraAutoria;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeporteCalendario extends Model
{
    use PerteneceASede;
    use RegistraAutoria;
    use SoftDeletes;

    public const TIPO_ESPORADICA = 'esporadica';

    public const TIPO_RECURRENTE = 'recurrente';

    public const TIPO_TORNEO = 'torneo';

    public const TIPOS = [
        self::TIPO_ESPORADICA => 'Una sola vez',
        self::TIPO_RECURRENTE => 'Se repite cada semana',
        self::TIPO_TORNEO     => 'Torneo',
    ];

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

    public const MAX_DIAS_RANGO = 366;

    protected $table = 'calendario_deportivo';

    protected $primaryKey = 'id_cld';

    protected $fillable = [
        'nombre_cld',
        'descripcion_cld',
        'fecha_inicio_cld',
        'fecha_fin_cld',
        'tipo_cld',
        'dias_semana_cld',
        'seccion_cld',
        'con_inscripcion_cld',
        'prg_id',
        'sed_id',
        'use_id',
        'status_id',
    ];

    protected $casts = [
        'fecha_inicio_cld'    => 'date',
        'fecha_fin_cld'       => 'date',
        'con_inscripcion_cld' => 'boolean',
        'prg_id'              => 'integer',
        'sed_id'              => 'integer',
        'status_id'           => 'integer',
    ];

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sed_id', 'id_sed');
    }

    public function coordinador()
    {
        return $this->belongsTo(Usuario::class, 'use_id', 'id_use');
    }

    public function programa()
    {
        return $this->belongsTo(Pnf::class, 'prg_id', 'id_prg');
    }

    public function horarios()
    {
        return $this->hasMany(DeporteHorario::class, 'cld_id', 'id_cld');
    }

    public function bloques()
    {
        return $this->horarios()->orderBy('hora_inicio_hde')->orderBy('id_hde');
    }

    public function scopeActividades(Builder $consulta): Builder
    {
        return $consulta->where($this->qualifyColumn('tipo_cld'), '!=', self::TIPO_TORNEO);
    }

    public function esEsporadica(): bool
    {
        return $this->tipo_cld === self::TIPO_ESPORADICA;
    }

    public function esRecurrente(): bool
    {
        return $this->tipo_cld === self::TIPO_RECURRENTE;
    }

    public function esTorneo(): bool
    {
        return $this->tipo_cld === self::TIPO_TORNEO;
    }

    public function estaActiva(): bool
    {
        return (int) $this->status_id === 1;
    }

    public function usaInscritos(): bool
    {
        return $this->esTorneo() || (bool) $this->con_inscripcion_cld;
    }

    public function tipoLegible(): string
    {
        return self::TIPOS[$this->tipo_cld] ?? ucfirst((string) $this->tipo_cld);
    }

    public static function normalizarDia(?string $dia): ?string
    {
        if ($dia === null || trim($dia) === '') {
            return null;
        }

        $dia = strtr(mb_strtolower(trim($dia), 'UTF-8'), ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u']);

        return in_array($dia, self::DIAS, true) ? $dia : null;
    }

    public static function normalizarDias($dias): array
    {
        $validos = [];

        foreach ((array) $dias as $dia) {
            if ($clave = self::normalizarDia(is_string($dia) ? $dia : null)) {
                $validos[$clave] = true;
            }
        }

        return array_values(array_filter(self::DIAS, fn ($dia) => isset($validos[$dia])));
    }

    public static function diaDeFecha($fecha): string
    {
        return self::DIAS[Carbon::parse($fecha)->dayOfWeekIso];
    }

    public static function listaLegible(array $partes): string
    {
        if (count($partes) <= 1) {
            return (string) ($partes[0] ?? '');
        }

        $ultimo = array_pop($partes);

        return implode(', ', $partes) . ' y ' . $ultimo;
    }

    public function dias(): array
    {
        return self::normalizarDias(explode(',', (string) $this->dias_semana_cld));
    }

    public function diasLegibles(): string
    {
        return self::listaLegible(array_map(fn ($dia) => mb_strtolower(self::DIAS_LEGIBLES[$dia]), $this->dias()));
    }

    public function fechaFinEfectiva(): Carbon
    {
        return $this->esEsporadica() ? $this->fecha_inicio_cld->copy()->startOfDay() : $this->fecha_fin_cld->copy()->startOfDay();
    }

    public function fechasEntre($desde, $hasta): array
    {
        $inicio = $this->fecha_inicio_cld->copy()->startOfDay();
        $fin = $this->fechaFinEfectiva();
        $desde = Carbon::parse($desde)->startOfDay();
        $hasta = Carbon::parse($hasta)->startOfDay();

        if ($desde->lt($inicio)) {
            $desde = $inicio->copy();
        }

        if ($hasta->gt($fin)) {
            $hasta = $fin->copy();
        }

        $dias = $this->esEsporadica() ? [] : $this->dias();
        $fechas = [];

        for ($dia = $desde->copy(), $i = 0; $dia->lte($hasta) && $i <= self::MAX_DIAS_RANGO; $dia->addDay(), $i++) {
            if ($dias === [] || in_array(self::diaDeFecha($dia), $dias, true)) {
                $fechas[] = $dia->toDateString();
            }
        }

        return $fechas;
    }

    public function fechas(): array
    {
        return $this->fechasEntre($this->fecha_inicio_cld, $this->fechaFinEfectiva());
    }

    public function aplicaEnFecha($fecha): bool
    {
        $fecha = Carbon::parse($fecha)->toDateString();

        return in_array($fecha, $this->fechasEntre($fecha, $fecha), true);
    }

    public function descripcionCuando(): string
    {
        $inicio = $this->fecha_inicio_cld;

        if ($this->esEsporadica()) {
            return self::DIAS_LEGIBLES[self::diaDeFecha($inicio)] . ' ' . $inicio->format('d/m/Y');
        }

        $rango = 'del ' . $inicio->format('d/m/Y') . ' al ' . $this->fecha_fin_cld->format('d/m/Y');

        if ($this->dias() === []) {
            return $inicio->equalTo($this->fecha_fin_cld) ? self::DIAS_LEGIBLES[self::diaDeFecha($inicio)] . ' ' . $inicio->format('d/m/Y') : ucfirst($rango);
        }

        return ucfirst($this->diasLegibles()) . ', ' . $rango;
    }

    public function seccionLegible(): ?string
    {
        $texto = trim(($this->programa?->nombre_prg ?? '') . ' ' . ($this->seccion_cld ?? ''));

        return $texto === '' ? null : $texto;
    }
}
