<?php

namespace App\Models\salud;

use App\Models\Persona;
use App\Models\Usuario;
use App\Traits\PerteneceASedePorRelacion;
use App\Traits\RegistraAutoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class Consulta extends Model
{
    use PerteneceASedePorRelacion;
    use RegistraAutoria;
    use SoftDeletes;

    protected $relacionSede = 'consultorio';

    protected $table = 'consulta_medica';

    protected $primaryKey = 'id_cme';

    /** Estados de la receta asociada */
    public const RECETA_ABIERTA = 1;
    public const RECETA_CERRADA = 2;

    /** Filtros de estado disponibles en el listado */
    public const FILTRO_PENDIENTE  = 'pendiente';
    public const FILTRO_COMPLETADO = 'completado';

    /** Filtros de estado disponibles en estadísticas */
    public const ESTADOS_REPORTE = [
        'con_receta',
        'sin_receta',
        'completado',
        'sin_dispensacion',
        'con_pendientes',
    ];

    /** Relaciones a cargar según la pantalla */
    public const CARGA_RECETACION = [
        'paciente',
        'medico',
        'receta.detalles.producto',
        'receta.detalles.unidad',
    ];

    public const CARGA_DISPENSACION = [
        'paciente',
        'medico',
        'consultorio',
        'enfermedades',
        'receta.detalles.producto.unidad',
        'receta.detalles.unidad',
        'receta.detalles.dispensaciones.lote',
        'receta.detalles.dispensaciones.usuario.persona',
    ];

    public const CARGA_HISTORICO = [
        'paciente',
        'medico',
        'consultorio',
        'enfermedades',
        'receta.paciente',
        'receta.medico',
        'receta.detalles.producto',
        'receta.detalles.unidad',
        'receta.detalles.dispensaciones.lote',
        'receta.detalles.dispensaciones.sede',
        'receta.detalles.dispensaciones.usuario.persona',
    ];

    public const CARGA_RECIPE = [
        'paciente',
        'medico',
        'consultorio',
        'receta.detalles.producto',
        'receta.detalles.unidad',
    ];

    protected $fillable = [
        'paciente_per_id',
        'medico_per_id',
        'csr_id',
        'fecha_cme',
        'motivo_cme',
        'observaciones_cme',
        'diagnostico_cme',
        'motivo_registro_tardio_cme',
        'use_id',
        'status_id',
    ];

    protected $casts = [
        'fecha_cme' => 'date',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'paciente_per_id', 'id_per');
    }

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'medico_per_id', 'id_per');
    }

    public function consultorio(): BelongsTo
    {
        return $this->belongsTo(Consultorio::class, 'csr_id', 'id_csr');
    }

    public function enfermedades(): BelongsToMany
    {
        return $this->belongsToMany(Enfermedad::class, 'consulta_enfermedad', 'cme_id', 'efm_id', 'id_cme', 'id_efm')
            ->withTimestamps();
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'use_id', 'id_use');
    }

    public function receta(): HasOne
    {
        return $this->hasOne(RecetasMedica::class, 'cme_id', 'id_cme');
    }

    public function fechaRegistro(): string
    {
        return ($this->created_at ?? now())->toDateString();
    }

    public function esRegistroTardio(): bool
    {
        return $this->fecha_cme !== null && $this->fecha_cme->toDateString() !== $this->fechaRegistro();
    }

    public function getPasoActualAttribute(): string
    {
        if (!$this->relationLoaded('receta')) {
            $this->load('receta');
        }

        if (!$this->receta) {
            return 'receta';
        }

        return (int) $this->receta->estado_rme === self::RECETA_CERRADA ? 'completa' : 'dispensacion';
    }

    /**
     * Indica si la receta de la consulta ya tiene entregas registradas.
     */
    public function tieneDispensaciones(): bool
    {
        if (!$this->receta) {
            return false;
        }

        return Dispensacion::where('rme_id', $this->receta->id_rme)->exists();
    }

    /**
     * Una consulta solo es editable mientras no tenga entregas.
     */
    public function esEditable(): bool
    {
        return !$this->tieneDispensaciones();
    }

    /**
     * ID del paciente que se usará en la receta.
     */
    public function pacienteIdParaReceta(): ?int
    {
        return $this->paciente_per_id ?? $this->paciente?->id_per;
    }

    /**
     * ID del médico que se usará en la receta.
     */
    public function medicoIdParaReceta(?int $usuarioFallback = null): ?int
    {
        return $this->medico_per_id
            ?? $this->medico?->id_per
            ?? $usuarioFallback;
    }

    public static function crear(array $data, int $creadoPor): self
    {
        return self::create(array_merge($data, ['use_id' => $creadoPor]));
    }

    /**
     * Registra una consulta nueva junto con sus enfermedades.
     */
    public static function registrar(array $data, array $enfermedadesIds, int $creadoPor): self
    {
        return DB::transaction(function () use ($data, $enfermedadesIds, $creadoPor) {
            $consulta = self::crear($data, $creadoPor);
            $consulta->enfermedades()->attach($enfermedadesIds);

            return $consulta;
        });
    }

    /**
     * Actualiza la consulta, sus enfermedades y mantiene la receta sincronizada.
     */
    public function actualizarConEnfermedades(array $data, array $enfermedadesIds): void
    {
        DB::transaction(function () use ($data, $enfermedadesIds) {
            $this->update($data);
            $this->enfermedades()->sync($enfermedadesIds);
            $this->sincronizarReceta();
        });
    }

    /**
     * Copia paciente y médico de la consulta a la receta (si existe).
     */
    public function sincronizarReceta(): void
    {
        $this->receta?->update([
            'paciente_per_id' => $this->paciente_per_id,
            'medico_per_id'   => $this->medico_per_id,
        ]);
    }

    public static function listar(
        ?string $buscar = null,
        ?int $consultorioId = null,
        ?int $medicoId = null,
        ?string $rangoFechas = null,
        ?string $estado = null
    ): LengthAwarePaginator {
        return self::with(['paciente', 'medico', 'consultorio', 'receta.detalles.dispensaciones'])
            ->buscarPaciente($buscar)
            ->delConsultorio($consultorioId)
            ->delMedico($medicoId)
            ->enRangoFechas($rangoFechas)
            ->conEstadoFlujo($estado)
            ->latest('consulta_medica.fecha_cme')
            ->latest('consulta_medica.id_cme')
            ->paginate(10)
            ->withQueryString();
    }

    public function scopeBuscarPaciente(Builder $query, ?string $buscar): Builder
    {
        if (!$buscar) {
            return $query;
        }

        return $query->whereHas('paciente', function ($q) use ($buscar) {
            $q->where('nombres_per', 'like', "%{$buscar}%")
                ->orWhere('apellidos_per', 'like', "%{$buscar}%")
                ->orWhere('cedula_per', 'like', "%{$buscar}%");
        });
    }

    /**
     * Rango con formato "Y-m-d to Y-m-d" (flatpickr) o una sola fecha.
     */
    public function scopeEnRangoFechas(Builder $query, ?string $rangoFechas): Builder
    {
        if (!$rangoFechas) {
            return $query;
        }

        $fechas = explode(' to ', $rangoFechas);

        return count($fechas) === 2
            ? $query->whereBetween('consulta_medica.fecha_cme', [$fechas[0], $fechas[1]])
            : $query->whereDate('consulta_medica.fecha_cme', $fechas[0]);
    }

    /**
     * Estado del flujo usado en el listado (pendiente / completado).
     */
    public function scopeConEstadoFlujo(Builder $query, ?string $estado): Builder
    {
        return match ($estado) {
            self::FILTRO_PENDIENTE => $query->where(function ($sub) {
                $sub->doesntHave('receta')
                    ->orWhereHas('receta', fn($r) => $r->where('receta_medica.estado_rme', self::RECETA_ABIERTA));
            }),
            self::FILTRO_COMPLETADO => $query->whereHas(
                'receta',
                fn($r) => $r->where('receta_medica.estado_rme', self::RECETA_CERRADA)
            ),
            default => $query,
        };
    }

    public function scopeDelPeriodo($query, ?string $inicio, ?string $fin)
    {
        if ($inicio && $fin) {
            $query->whereBetween('consulta_medica.fecha_cme', [$inicio, $fin]);
        }
        return $query;
    }

    public function scopeDelMedico($query, ?int $medicoId)
    {
        if ($medicoId) {
            $query->where('consulta_medica.medico_per_id', $medicoId);
        }
        return $query;
    }

    public function scopeDelConsultorio($query, ?int $consultorioId)
    {
        if ($consultorioId) {
            $query->where('consulta_medica.csr_id', $consultorioId);
        }
        return $query;
    }

    public function scopeConEnfermedades($query, array $enfermedadIds)
    {
        if (!empty($enfermedadIds)) {
            $query->whereHas('enfermedades', fn($q) => $q->whereIn('enfermedad.id_efm', $enfermedadIds));
        }
        return $query;
    }

    public function scopeConEstadoReceta($query, ?string $estado)
    {
        if (!$estado) {
            return $query;
        }

        $detalleConPendiente = function ($d) {
            $d->whereColumn('detalle_receta_medica.cantidad_drm', '<', 'detalle_receta_medica.cantidad_prescrita_drm');
        };

        return match ($estado) {
            // Tiene receta (en cualquier estado)
            'con_receta' => $query->has('receta'),

            // No tiene receta
            'sin_receta' => $query->doesntHave('receta'),

            // Receta completamente dispensada: ≥1 detalle y ninguno pendiente
            'completado' => $query->whereHas('receta', function ($r) use ($detalleConPendiente) {
                $r->has('detalles')
                    ->whereDoesntHave('detalles', $detalleConPendiente);
            }),

            // Receta emitida pero sin ninguna dispensación
            'sin_dispensacion' => $query->whereHas('receta', function ($r) {
                $r->whereDoesntHave('detalles.dispensaciones');
            }),

            // Todo lo que NO está completado (catch-all)
            'con_pendientes' => $query->whereDoesntHave('receta', function ($r) use ($detalleConPendiente) {
                $r->has('detalles')
                    ->whereDoesntHave('detalles', $detalleConPendiente);
            }),

            default => $query,
        };
    }

    public function scopeConPacienteFiltros($query, ?string $perfil, ?string $pnf)
    {
        if (!$perfil && !$pnf) {
            return $query;
        }

        return $query->whereHas('paciente', function ($u) use ($perfil, $pnf) {
            if ($perfil) {
                $u->whereHas('perfil', fn($p) => $p->where('nombre_perfil', $perfil));
            }
            if ($pnf) {
                $u->whereHas('personaPnf.pnf', fn($p) => $p->where('nombre_prg', $pnf));
            }
        });
    }
}