<?php
namespace App\Services\becas;

use App\Models\Becas\JornadaBeca;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Becas\Lapso;
use App\Models\Becas\Beneficio;
use App\Models\Becas\JornadaCriterio;
use Exception;

class JornadaBecasServices
{
    protected const CACHE_KEY_ACTIVA = 'jornada_beca_activa';

    public function obtenerJornadaActiva()
    {
        $hoy = now()->toDateString();

        $this->desactivarJornadasExpiradas();
        

        return Cache::remember(self::CACHE_KEY_ACTIVA, now()->addHours(12), function () use ($hoy) {
            return JornadaBeca::where('activa', 1)
                ->whereDate('fecha_inicio_solicitud', '<=', $hoy)
                ->whereDate('fecha_fin_solicitud', '>=', $hoy)
                ->get();
        });
    }

    public function crearJornada(array $validated): JornadaBeca
    {
        DB::beginTransaction();
        try {
            $criterios = $validated['criterios'] ?? [];
            unset($validated['criterios']);

            $validated['nombre_jornada'] = $this->normalizarNombreConLapso(
                $validated['nombre_jornada'],
                $validated['lapsos_id']
            );
            $validated['cupos_asignados'] = 0;

            $this->validarCuposDisponibles($validated['beneficio_id'], $validated['cupos_maximos']);

            $jornada = JornadaBeca::create($validated);

            $this->guardarCriterios($jornada, $criterios);

            $this->limpiarCacheJornada();
            DB::commit();

            return $jornada;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function actualizarJornada(int $id, array $datos): JornadaBeca
    {
        return DB::transaction(function () use ($id, $datos) {
            $jornada = JornadaBeca::findOrFail($id);

            $criterios = $datos['criterios'] ?? null;
            unset($datos['criterios']);

            if ($jornada->fecha_fin_solicitud && $jornada->fecha_fin_solicitud->isPast()) {
                throw new Exception('No se puede actualizar una jornada que ya ha expirado.');
            }

            $jornadaIniciada = $jornada->fecha_inicio_solicitud && $jornada->fecha_inicio_solicitud->isPast();

            if ($jornadaIniciada) {
                $inicioOriginal = $jornada->fecha_inicio_solicitud->format('Y-m-d');
                $nuevoInicio    = isset($datos['fecha_inicio_solicitud'])
                    ? date('Y-m-d', strtotime($datos['fecha_inicio_solicitud']))
                    : '';

                if ($jornada->beneficio_id != $datos['beneficio_id']
                    || $jornada->lapsos_id != $datos['lapsos_id']
                    || $inicioOriginal !== $nuevoInicio
                    || $jornada->activa != ($datos['activa'] ?? 0)) {
                    throw new Exception('No se pueden modificar campos críticos en una jornada que ya ha iniciado.');
                }
            }

            $this->validarCuposDisponibles($datos['beneficio_id'], $datos['cupos_maximos']);

            $datos['nombre_jornada'] = $this->normalizarNombreConLapso(
                $datos['nombre_jornada'],
                $datos['lapsos_id']
            );

            $jornada->update($datos);

            if ($criterios !== null && !$jornadaIniciada) {
                $jornada->criterios()->delete();
                $this->guardarCriterios($jornada, $criterios);
            }

            $this->limpiarCacheJornada();

            return $jornada;
        });
    }

    private function guardarCriterios(JornadaBeca $jornada, array $criterios): void
    {
        if (empty($criterios)) return;

        $rows = collect($criterios)
            ->filter(fn ($c) => !empty($c['id_pregunta']))
            ->map(fn ($c) => [
                'id_jornada'      => $jornada->id,
                'id_pregunta'     => $c['id_pregunta'],
                'operador'        => $c['operador'] ?? null,
                'valor_esperado'  => $c['valor_esperado'] ?? null,
                'es_eliminatoria' => !empty($c['es_eliminatoria']),
                'peso'            => $c['peso'] ?? 0,
                'created_at'      => now(),
                'updated_at'      => now(),
            ])
            ->values()
            ->all();

        if (!empty($rows)) {
            JornadaCriterio::insert($rows);
        }
    }

    public function desactivarJornada(int $id): JornadaBeca
    {
        $jornada = JornadaBeca::findOrFail($id);
        $jornada->update(['activa' => 0]);

        $this->limpiarCacheJornada();

        return $jornada;
    }

    public function activarJornada(int $id): JornadaBeca
    {
        $jornada = JornadaBeca::findOrFail($id);
        $jornada->update(['activa' => 1]);

        $this->limpiarCacheJornada();

        return $jornada;
    }

    public function index()
    {
        $this->desactivarJornadasExpiradas();

        $buscar    = request('buscar');
        $beneficio = request('beneficio');
        $activa    = request('activa', 1);

        $jornadas = JornadaBeca::with(['beneficio', 'lapso'])
            ->when($buscar, fn ($q) => $q->where('nombre_jornada', 'like', "%{$buscar}%"))
            ->when($beneficio, fn ($q) => $q->where('beneficio_id', $beneficio))
            ->where('activa', $activa)
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $beneficios = Beneficio::orderBy('nombre_beneficio')->get();
        $lapsoActual = Lapso::where('es_actual', 1)->first();

        return view('admin.becas.jornada.index', compact('jornadas', 'beneficios', 'lapsoActual'));
    }

    public function limpiarCacheJornada(): void
    {
        Cache::forget(self::CACHE_KEY_ACTIVA);
    }

    private function validarCuposDisponibles(int $beneficioId, int $cuposMaximos): void
    {
        $beneficio = Beneficio::findOrFail($beneficioId);
        if ($beneficio->cupones_disponibles < $cuposMaximos) {
            throw new Exception('El número de cupos máximos excede los cupos disponibles del beneficio.');
        }
    }

    private function normalizarNombreConLapso(string $nombreJornada, int $lapsoId): string
    {
        $lapsoElegido = Lapso::findOrFail($lapsoId)->codigo;
        if (!str_contains($nombreJornada, $lapsoElegido)) {
            $nombreJornada .= ' ' . $lapsoElegido;
        }
        return $nombreJornada;
    }

    private function desactivarJornadasExpiradas()
    {
        $hoy = now()->toDateString();

        $jornadasExpiradas = JornadaBeca::where('activa', 1)
            ->whereDate('fecha_fin_solicitud', '<', $hoy);
        
        if ($jornadasExpiradas->exists()) {
            $jornadasExpiradas->update(['activa' => 0]);
            $this->limpiarCacheJornada();
        }
    }
}