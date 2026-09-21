<?php

namespace App\Models\salud;

use App\Models\Persona;
use App\Models\Usuario;
use App\Models\salud\RecetasMedica;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class Consulta extends Model
{
    protected $table = 'consultas';

    protected $fillable = [
        'id_persona',
        'medico_id',
        'consultorio_id',
        'enfermedad_id',
        'fecha',
        'motivo',
        'observaciones',
        'diagnostico',
        'creado_por',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function paciente()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    public function medico()
    {
        return $this->belongsTo(Persona::class, 'medico_id', 'id_persona');
    }

    public function consultorio()
    {
        return $this->belongsTo(Consultorio::class, 'consultorio_id');
    }

    public function enfermedades()
    {
        return $this->belongsToMany(Enfermedad::class, 'consulta_enfermedad');
    }

    public function creadoPor()
    {
        return $this->belongsTo(Usuario::class, 'creado_por', 'id_usuario');
    }

    public function receta()
    {
        return $this->hasOne(RecetasMedica::class, 'consulta_id');
    }

    public function getPasoActualAttribute(): string
    {
        if (!$this->relationLoaded('receta')) {
            $this->load('receta.detalles.dispensaciones');
        }

        if (!$this->receta) {
            return 'receta';
        }

        if ($this->receta->tieneItemsPendientes()) {
            return 'dispensacion';
        }

        return 'completa';
    }

    public static function crear(array $data, int $creadoPor): self
    {
        return self::create(array_merge($data, ['creado_por' => $creadoPor]));
    }

    public static function listar(
        ?string $buscar = null,
        ?int $consultorioId = null,
        ?int $medicoId = null,
        ?string $rangoFechas = null,
        ?string $estado = null
    ) {
        $query = self::with(['paciente', 'medico', 'consultorio', 'receta.detalles.dispensaciones'])
            ->latest('fecha')
            ->latest('id');

        // Búsqueda
        if ($buscar) {
            $query->whereHas('paciente', function ($q) use ($buscar) {
                $q->where('nombre_persona', 'like', "%{$buscar}%")
                    ->orWhere('apellido_persona', 'like', "%{$buscar}%")
                    ->orWhere('cedula_persona', 'like', "%{$buscar}%");
            });
        }

        // Consultorio
        if ($consultorioId) {
            $query->where('consultorio_id', $consultorioId);
        }

        // Médico
        if ($medicoId) {
            $query->where('medico_id', $medicoId);
        }

        //LÓGICA DE RANGO DE FECHAS
        if ($rangoFechas) {
            $fechas = explode(' to ', $rangoFechas);

            if (count($fechas) === 2) {
                // Seleccionó dos fechas (Rango completo)
                $query->whereBetween('fecha', [$fechas[0], $fechas[1]]);
            } else {
                // Seleccionó una sola fecha (hizo doble clic en el mismo día)
                $query->whereDate('fecha', $fechas[0]);
            }
        }

        // Estado
        if ($estado) {
            if ($estado === 'pendiente') {
                $query->where(function ($sub) {
                    $sub->doesntHave('receta')
                        ->orWhereHas('receta', function ($r) {
                            $r->where('estado', 1);
                        });
                });
            } elseif ($estado === 'completado') {
                $query->whereHas('receta', function ($r) {
                    $r->where('estado', 2);
                });
            }
        }

        return $query->paginate(10)->withQueryString();
    }

    // app/Models/salud/Consulta.php

    public function scopeDelPeriodo($query, ?string $inicio, ?string $fin)
    {
        if ($inicio && $fin) {
            $query->whereBetween('fecha', [$inicio, $fin]);
        }
        return $query;
    }

    public function scopeDelMedico($query, ?int $medicoId)
    {
        if ($medicoId) $query->where('medico_id', $medicoId);
        return $query;
    }

    public function scopeDelConsultorio($query, ?int $consultorioId)
    {
        if ($consultorioId) $query->where('consultorio_id', $consultorioId);
        return $query;
    }

    public function scopeConEnfermedades($query, array $enfermedadIds)
    {
        if (!empty($enfermedadIds)) {
            $query->whereHas('enfermedades', fn($q) => $q->whereIn('enfermedades.id', $enfermedadIds));
        }
        return $query;
    }

    public function scopeConEstadoReceta($query, ?string $estado)
    {
        if (!$estado) return $query;

        // Sub-consulta reutilizable: "detalle con cantidad pendiente"
        $detalleConPendiente = function ($d) {
            $d->whereRaw(
                '(SELECT COALESCE(SUM(cantidad),0) FROM dispensacions 
              WHERE detalle_receta_medica_id = detalle_recetas_medicas.id) 
             < detalle_recetas_medicas.cantidad'
            );
        };

        return match ($estado) {
            // ── Tiene receta (en cualquier estado) ───────────────────
            'con_receta' => $query->has('receta'),

            // ── No tiene receta ──────────────────────────────────────
            'sin_receta' => $query->doesntHave('receta'),

            // ── Receta completamente dispensada ──────────────────────
            // Tiene al menos 1 detalle Y ningún detalle con pendiente
            'completado' => $query->whereHas('receta', function ($r) use ($detalleConPendiente) {
                $r->has('detalles')
                    ->whereDoesntHave('detalles', $detalleConPendiente);
            }),

            // ── Receta emitida pero SIN ninguna dispensación ────────
            'sin_dispensacion' => $query->whereHas('receta', function ($r) {
                $r->whereDoesntHave('detalles.dispensaciones');
            }),

            // ── Todo lo que NO está completado (catch-all) ──────────
            // Incluye: sin receta, con receta sin detalles,
            // con receta con pendientes, anuladas, etc.
            'con_pendientes' => $query->whereDoesntHave('receta', function ($r) use ($detalleConPendiente) {
                $r->has('detalles')
                    ->whereDoesntHave('detalles', $detalleConPendiente);
            }),

            default => $query,
        };
    }

    public function scopeConPacienteFiltros($query, ?string $perfil, ?string $pnf)
    {
        if (!$perfil && !$pnf) return $query;

        return $query->whereHas('paciente', function ($u) use ($perfil, $pnf) {
            if ($perfil) {
                $u->whereHas('perfil', fn($p) => $p->where('nombre_perfil', $perfil));
            }
            if ($pnf) {
                $u->whereHas('personaPnf.pnf', fn($p) => $p->where('nombre_pnf', $pnf));
            }
        });
    }
}
