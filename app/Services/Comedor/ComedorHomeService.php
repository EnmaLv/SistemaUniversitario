<?php

namespace App\Services\Comedor;

use App\Models\DetalleRegistroDiario;
use App\Models\Lote;
use App\Models\Modulo;
use App\Models\Producto;
use App\Models\Receta;
use App\Models\RecetaIngrediente;
use App\Models\SobranteComedor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ComedorHomeService
{
    /**
     * Devuelve todos los datos iniciales del panel estadístico de Comedor.
     */
    public function getDashboardData(array $filtros = []): array
    {
        $fechaInicio = $filtros['start_date'] ?? Carbon::now()->subDays(30)->toDateString();
        $fechaFin    = $filtros['end_date']   ?? Carbon::now()->toDateString();

        $registros = $this->consultarRegistros($filtros, $fechaInicio, $fechaFin);
        $resumen   = $this->calcularResumen($registros, $fechaInicio, $fechaFin);

        return [
            'fechaInicio' => $fechaInicio,
            'fechaFin'    => $fechaFin,
            'registros'   => $registros,
            'resumen'     => $resumen,
            'pnfs'        => $this->pnfsDisponibles(),
        ];
    }

    /**
     * Query armada con joins para obtener los registros diarios + datos de persona y PNF.
     */
    protected function consultarRegistros(array $f, string $inicio, string $fin)
    {
        $query = \App\Support\Sedes\SedeActiva::filtrar(DB::table('registro_diario_c as r'), 'r.sed_id')
            ->join('persona as p', 'r.per_id', '=', 'p.id_per')
            ->leftJoin('persona_programa as pp', 'r.ppr_id', '=', 'pp.id_ppr')
            ->leftJoin('programa as n', 'pp.prg_id', '=', 'n.id_prg')
            ->whereBetween('r.fecha_regis_diario_c', [$inicio, $fin])
            ->select(
                'r.id',
                'r.fecha_regis_diario_c',
                'r.hora',
                'r.per_id',
                'r.ppr_id',
                'p.nombres_per',
                'p.apellidos_per',
                'p.genero_per',
                'n.id_prg',
                'n.nombre_prg'
            );

        if (!empty($f['pnf_id'])) {
            $query->where('n.id_prg', $f['pnf_id']);
        }

        return $query->orderBy('r.fecha_regis_diario_c', 'desc')->get();
    }

    /**
     * Lista de PNF disponibles para el filtro.
     */
    protected function pnfsDisponibles()
    {
        return DB::table('programa')->orderBy('nombre_prg')->get(['id_prg', 'nombre_prg']);
    }

    /**
     * IDs de productos que pertenecen al módulo "comedor" (categoría → tipo_producto → módulo).
     */
    protected function productosDelComedor(): array
    {
        $comedor = Modulo::where('key', 'comedor')->first();
        if (!$comedor) {
            return [];
        }

        return Producto::whereHas('categoria', function ($q) use ($comedor) {
            $q->whereHas('tipoProducto', function ($t) use ($comedor) {
                $t->where('modulo_id', $comedor->id);
            });
        })->pluck('id')->toArray();
    }

    /**
     * Toda la lógica de cálculo del resumen.
     */
    protected function calcularResumen($registros, $fechaInicio, $fechaFin): array
    {
        $hoy    = Carbon::now();
        $limite = Carbon::now()->addDays(7);

        // ── Productos del comedor ──
        $productosIds = $this->productosDelComedor();

        // ── Porciones servidas (detalle_registro_diarios) ──
        $totalPorciones = (float) DetalleRegistroDiario::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->sum('cantidad_servido');

        // ── Sobrantes ──
        $sobrantes       = SobranteComedor::whereBetween('fecha', [$fechaInicio, $fechaFin])->get();
        $totalSobrantes  = (float) $sobrantes->sum('cantidad_sobrante');

        // ── Recetas ──
        $totalRecetas = Receta::where('estado', 1)->count();

        // ── Lotes del comedor ──
        $lotesVencidos  = 0;
        $lotesPorVencer = 0;
        $lotesVigentes  = 0;

        if (!empty($productosIds)) {
            $lotes = Lote::whereIn('producto_id', $productosIds)
                ->where('estado', 1)
                ->get(['id', 'fecha_vencimiento']);

            foreach ($lotes as $lote) {
                if (!$lote->fecha_vencimiento) {
                    $lotesVigentes++;
                    continue;
                }
                $fecha = Carbon::parse($lote->fecha_vencimiento);
                if ($fecha->lte($hoy)) {
                    $lotesVencidos++;
                } elseif ($fecha->lte($limite)) {
                    $lotesPorVencer++;
                } else {
                    $lotesVigentes++;
                }
            }
        }

        // ── Top ingredientes (productos del comedor) por gramos usados en recetas ──
        $topProductos = [];
        if (!empty($productosIds)) {
            $topProductos = RecetaIngrediente::whereIn('producto_id', $productosIds)
                ->where('estado', 1)
                ->select('producto_id', DB::raw('SUM(cantidad_gramos) as total_gramos'))
                ->groupBy('producto_id')
                ->orderByDesc('total_gramos')
                ->take(8)
                ->with('producto:id,nombre')
                ->get()
                ->mapWithKeys(function ($item) {
                    $nombre = $item->producto->nombre ?? ('Producto #' . $item->producto_id);
                    return [$nombre => (float) $item->total_gramos];
                })
                ->toArray();
        }

        // ── Top recetas por número de ingredientes ──
        $topRecetas = Receta::where('estado', 1)
            ->withCount('recetaIngredientes')
            ->orderByDesc('receta_ingredientes_count')
            ->take(8)
            ->get()
            ->mapWithKeys(fn ($r) => [$r->nombre => $r->receta_ingredientes_count])
            ->toArray();

        // ── Agrupaciones ──
        $resumen = [
            'total_servicios'      => $registros->count(),
            'total_personas'       => 0,
            'total_porciones'      => $totalPorciones,
            'total_sobrantes'      => round($totalSobrantes, 2),
            'total_recetas'        => $totalRecetas,
            'total_productos'      => count($productosIds),
            'lotes_vencidos'       => $lotesVencidos,
            'lotes_por_vencer'     => $lotesPorVencer,
            'lotes_vigentes'       => $lotesVigentes,
            'servicios_promedio'   => 0,
            'hora_pico'            => 'N/A',
            'comparativa_servicios' => 0,
            'genero' => ['masculino' => 0, 'femenino' => 0, 'otro' => 0],
            'por_hora'              => [],
            'por_pnf'               => [],
            'por_dia'               => [],
            'flujo_semanal'         => [],
            'top_productos'         => $topProductos,
            'top_recetas'           => $topRecetas,
            'sobrantes_por_motivo'  => [],
        ];

        $personas       = [];
        $horasBloques   = [];
        $pnfBloques     = [];
        $diaBloques     = [];
        $semanaBloques  = [];

        foreach ($registros as $r) {
            $personas[$r->per_id] = true;

            // Género
            $genero = strtolower(trim($r->genero_per ?? ''));
            if (in_array($genero, ['masculino', 'hombre', 'm'])) {
                $resumen['genero']['masculino']++;
            } elseif (in_array($genero, ['femenino', 'mujer', 'f'])) {
                $resumen['genero']['femenino']++;
            } else {
                $resumen['genero']['otro']++;
            }

            // Hora
            if ($r->hora) {
                try {
                    $bloque = Carbon::parse($r->hora)->format('h:00 A');
                    $horasBloques[$bloque] = ($horasBloques[$bloque] ?? 0) + 1;
                } catch (\Exception $e) {
                    // ignorar hora inválida
                }
            }

            // PNF
            $pnfNombre = $r->nombre_prg ?: 'Sin PNF';
            $pnfBloques[$pnfNombre] = ($pnfBloques[$pnfNombre] ?? 0) + 1;

            // Fecha
            if ($r->fecha_regis_diario_c) {
                $fecha = Carbon::parse($r->fecha_regis_diario_c);

                $diaKey = $fecha->format('Y-m-d');
                $diaBloques[$diaKey] = ($diaBloques[$diaKey] ?? 0) + 1;

                $semanaKey = $fecha->format('W-Y');
                $semanaBloques[$semanaKey] = ($semanaBloques[$semanaKey] ?? 0) + 1;
            }
        }

        $resumen['total_personas'] = count($personas);

        // Promedio semanal
        if ($resumen['total_servicios'] > 0 && $fechaInicio && $fechaFin) {
            $semanas = max(1, Carbon::parse($fechaInicio)->diffInDays(Carbon::parse($fechaFin))) / 7;
            $resumen['servicios_promedio'] = round($resumen['total_servicios'] / max(0.1, $semanas), 1);
        }

        // Ordenar
        arsort($horasBloques);
        arsort($pnfBloques);
        ksort($diaBloques);
        ksort($semanaBloques);

        $resumen['por_hora']        = $horasBloques;
        $resumen['por_pnf']         = $pnfBloques;
        $resumen['por_dia']         = $diaBloques;
        $resumen['flujo_semanal']   = $semanaBloques;
        $resumen['hora_pico']       = !empty($horasBloques) ? array_key_first($horasBloques) : 'N/A';

        // Sobrantes por motivo
        $sobrantesMotivos = [];
        foreach ($sobrantes as $s) {
            $motivo = $s->motivo ?: 'Sin motivo';
            $sobrantesMotivos[$motivo] = ($sobrantesMotivos[$motivo] ?? 0) + (float) $s->cantidad_sobrante;
        }
        arsort($sobrantesMotivos);
        $resumen['sobrantes_por_motivo'] = $sobrantesMotivos;

        // Comparativa vs período anterior
        if ($fechaInicio && $fechaFin) {
            $inicio     = Carbon::parse($fechaInicio);
            $fin        = Carbon::parse($fechaFin);
            $dias       = $inicio->diffInDays($fin);
            $prevFin    = $inicio->copy()->subDay();
            $prevInicio = $prevFin->copy()->subDays($dias);

            $prev = \App\Support\Sedes\SedeActiva::filtrar(DB::table('registro_diario_c'), 'sed_id')
                ->whereBetween('fecha_regis_diario_c', [
                    $prevInicio->toDateString(),
                    $prevFin->toDateString(),
                ])->count();

            if ($prev > 0) {
                $resumen['comparativa_servicios'] = round(
                    (($resumen['total_servicios'] - $prev) / $prev) * 100, 1
                );
            } elseif ($resumen['total_servicios'] > 0) {
                $resumen['comparativa_servicios'] = 100;
            }
        }

        return $resumen;
    }
}