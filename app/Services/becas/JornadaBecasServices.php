<?php
namespace App\Services\becas;

use App\Models\Becas\JornadaBeca;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
            return JornadaBeca::activos()
                ->whereDate('fecha_inicio_solicitud_jnb', '<=', $hoy)
                ->whereDate('fecha_fin_solicitud_jnb', '>=', $hoy)
                ->get();
        });
    }

    public function crearJornada(array $validated): JornadaBeca
    {
        DB::beginTransaction();
        try {
            $criterios = $validated['criterios'] ?? [];

            $datos = $this->datosJornada($validated);
            $datos['nombre_jornada_jnb'] = $this->normalizarNombreConLapso(
                $datos['nombre_jornada_jnb'],
                $datos['laa_id']
            );
            $datos['cupos_asignados_jnb'] = 0;

            $this->validarCuposDisponibles($datos['bes_id'], $datos['cupos_maximos_jnb']);

            $jornada = JornadaBeca::create($datos);

            $this->guardarCriterios($jornada, $criterios);

            $this->limpiarCacheJornada();
            DB::commit();

            return $jornada;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function actualizarJornada(int $id, array $validated): JornadaBeca
    {
        return DB::transaction(function () use ($id, $validated) {
            $jornada = JornadaBeca::findOrFail($id);

            $criterios = $validated['criterios'] ?? null;
            $datos = $this->datosJornada($validated);

            if ($jornada->fecha_fin_solicitud_jnb && $jornada->fecha_fin_solicitud_jnb->isPast()) {
                throw new Exception('No se puede actualizar una jornada que ya ha expirado.');
            }

            $jornadaIniciada = $jornada->fecha_inicio_solicitud_jnb && $jornada->fecha_inicio_solicitud_jnb->isPast();

            if ($jornadaIniciada) {
                $inicioOriginal = $jornada->fecha_inicio_solicitud_jnb->format('Y-m-d');
                $nuevoInicio    = !empty($datos['fecha_inicio_solicitud_jnb'])
                    ? date('Y-m-d', strtotime($datos['fecha_inicio_solicitud_jnb']))
                    : '';

                if ($jornada->bes_id != $datos['bes_id']
                    || $jornada->laa_id != $datos['laa_id']
                    || $inicioOriginal !== $nuevoInicio
                    || (int) $jornada->status_id !== $datos['status_id']) {
                    throw new Exception('No se pueden modificar campos críticos en una jornada que ya ha iniciado.');
                }
            }

            $this->validarCuposDisponibles($datos['bes_id'], $datos['cupos_maximos_jnb']);

            $datos['nombre_jornada_jnb'] = $this->normalizarNombreConLapso(
                $datos['nombre_jornada_jnb'],
                $datos['laa_id']
            );

            $jornada->update($datos);

            if ($criterios !== null && !$jornadaIniciada) {
                $jornada->criterios()->forceDelete();
                $this->guardarCriterios($jornada, $criterios);
            }

            $this->limpiarCacheJornada();

            return $jornada;
        });
    }

    private function datosJornada(array $validated): array
    {
        return [
            'nombre_jornada_jnb'         => $validated['nombre_jornada'],
            'descripcion_jornada_jnb'    => $validated['descripcion_jornada'] ?? null,
            'bes_id'                     => (int) $validated['beneficio_id'],
            'laa_id'                     => (int) $validated['lapsos_id'],
            'fecha_inicio_solicitud_jnb' => $validated['fecha_inicio_solicitud'],
            'fecha_fin_solicitud_jnb'    => $validated['fecha_fin_solicitud'],
            'cupos_maximos_jnb'          => (int) $validated['cupos_maximos'],
            'status_id'                  => !empty($validated['activa']) ? JornadaBeca::STATUS_ACTIVO : JornadaBeca::STATUS_INACTIVO,
        ];
    }

    private function guardarCriterios(JornadaBeca $jornada, array $criterios): void
    {
        if (empty($criterios)) return;

        $rows = collect($criterios)
            ->filter(fn ($c) => !empty($c['id_pregunta']))
            ->map(fn ($c) => [
                'jnb_id'              => $jornada->id_jnb,
                'pgb_id'              => $c['id_pregunta'],
                'operador_cjb'        => $c['operador'] ?? null,
                'valor_esperado_cjb'  => $c['valor_esperado'] ?? null,
                'es_eliminatoria_cjb' => !empty($c['es_eliminatoria']),
                'peso_cjb'            => $c['peso'] ?? 0,
                'status_id'           => JornadaCriterio::STATUS_ACTIVO,
                'created_id'          => Auth::id(),
                'created_at'          => now(),
                'updated_at'          => now(),
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
        $jornada->update(['status_id' => JornadaBeca::STATUS_INACTIVO]);

        $this->limpiarCacheJornada();

        return $jornada;
    }

    public function activarJornada(int $id): JornadaBeca
    {
        $jornada = JornadaBeca::findOrFail($id);
        $jornada->update(['status_id' => JornadaBeca::STATUS_ACTIVO]);

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
            ->when($buscar, fn ($q) => $q->where('nombre_jornada_jnb', 'like', "%{$buscar}%"))
            ->when($beneficio, fn ($q) => $q->where('bes_id', $beneficio))
            ->where('status_id', (int) $activa === 1 ? JornadaBeca::STATUS_ACTIVO : JornadaBeca::STATUS_INACTIVO)
            ->orderByDesc('id_jnb')
            ->paginate(10)
            ->withQueryString();

        $beneficios = Beneficio::orderBy('nombre_beneficio_bes')->get();
        $lapsoActual = Lapso::where('es_actual_laa', true)->first();

        return view('admin.becas.jornada.index', compact('jornadas', 'beneficios', 'lapsoActual'));
    }

    public function limpiarCacheJornada(): void
    {
        Cache::forget(self::CACHE_KEY_ACTIVA);
    }

    private function validarCuposDisponibles(int $beneficioId, int $cuposMaximos): void
    {
        $beneficio = Beneficio::findOrFail($beneficioId);
        if ($beneficio->cupones_disponibles_bes < $cuposMaximos) {
            throw new Exception('El número de cupos máximos excede los cupos disponibles del beneficio.');
        }
    }

    private function normalizarNombreConLapso(string $nombreJornada, int $lapsoId): string
    {
        $lapsoElegido = Lapso::findOrFail($lapsoId)->codigo_laa;
        if (!str_contains($nombreJornada, $lapsoElegido)) {
            $nombreJornada .= ' ' . $lapsoElegido;
        }
        return $nombreJornada;
    }

    private function desactivarJornadasExpiradas()
    {
        $hoy = now()->toDateString();

        $jornadasExpiradas = JornadaBeca::activos()
            ->whereDate('fecha_fin_solicitud_jnb', '<', $hoy);

        if ($jornadasExpiradas->exists()) {
            $jornadasExpiradas->update(['status_id' => JornadaBeca::STATUS_INACTIVO]);
            $this->limpiarCacheJornada();
        }
    }
}
