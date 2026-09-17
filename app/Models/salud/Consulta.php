<?php

namespace App\Models\salud;

use App\Models\Persona;
use App\Models\Usuario;
use App\Models\salud\RecetasMedica;

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
}
