<?php

namespace App\Http\Controllers\salud;

use App\Http\Controllers\Controller;
use App\Models\InventarioSedeLote;
use App\Models\Persona;
use App\Models\salud\Consulta;
use App\Models\salud\Consultorio;
use App\Models\salud\DetalleRecetasMedica;
use App\Models\salud\Dispensacion;
use App\Models\salud\Enfermedad;
use App\Models\salud\HorarioConsultorio;
use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\Unidad;
use App\Services\Salud\SaludHomeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ConsultaController extends Controller
{
    /**
     * Listado de consultas.
     */
    public function index(Request $request)
    {
        $consultorios = Consultorio::where('activo', true)->orderBy('nombre')->get();
        $medicos = HorarioConsultorio::usuariosElegibles();

        $consultas = Consulta::listar(
            $request->input('buscar'),
            $request->input('consultorio_id'),
            $request->input('medico_id'),
            $request->input('rango_fechas'),
            $request->input('estado')
        );

        return view('admin.salud.movimientos.consultas.index', compact('consultas', 'consultorios', 'medicos'));
    }

    /**
     * Paso 1: Formulario unificado para iniciar o editar una consulta en progreso.
     */
    public function create(Consulta $consulta = null)
    {
        if ($consulta && $consulta->exists && $this->tieneDispensaciones($consulta)) {
            return redirect()
                ->route('admin.salud.movimientos.consultas.show', $consulta)
                ->with('error', 'No se puede modificar la consulta porque ya cuenta con entregas registradas.');
        }

        if ($consulta && $consulta->exists) {
            $consulta->load(['enfermedades', 'paciente']);
        } else {
            $consulta = new Consulta();
        }

        $consultorios = Consultorio::where('activo', true)->orderBy('nombre')->get();
        $medicos     = HorarioConsultorio::usuariosElegibles();
        $personas    = Persona::where('estado', true)->orderBy('nombre_persona')->get();

        return view('admin.salud.movimientos.consultas.create', compact(
            'consulta',
            'consultorios',
            'medicos',
            'personas'
        ));
    }

    /**
     * Paso 1 (STORE): Guarda una consulta nueva.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_persona'     => 'required|exists:persona,id_persona',
            'medico_id'      => 'required|exists:persona,id_persona',
            'consultorio_id' => 'required|exists:consultorios,id',
            'fecha'          => 'required|date',
            'motivo'         => 'required|string',
            'diagnostico'    => 'required|string',
            'observaciones'  => 'nullable|string',
            'enfermedades'   => 'required|array|min:1',
        ]);

        $validated['observaciones'] = $validated['observaciones'] ?? '';
        $enfermedadesIds = $validated['enfermedades'];
        unset($validated['enfermedades']);

        $consulta = Consulta::crear($validated, auth()->id());
        $consulta->enfermedades()->attach($enfermedadesIds);

        return redirect()
            ->route('admin.salud.movimientos.consultas.recetacion', $consulta)
            ->with('success', 'Consulta registrada exitosamente. Continúe con la recetación.');
    }

    /**
     * Paso 1 (UPDATE): Actualiza la consulta si regresó desde el Paso 2 o 3.
     */
    public function update(Request $request, Consulta $consulta)
    {
        if ($this->tieneDispensaciones($consulta)) {
            return redirect()
                ->route('admin.salud.movimientos.consultas.show', $consulta)
                ->with('error', 'No se puede modificar la consulta porque ya cuenta con entregas registradas.');
        }

        $validated = $request->validate([
            'id_persona'     => 'required|exists:persona,id_persona',
            'medico_id'      => 'required|exists:persona,id_persona',
            'consultorio_id' => 'required|exists:consultorios,id',
            'fecha'          => 'required|date',
            'motivo'         => 'required|string',
            'diagnostico'    => 'required|string',
            'observaciones'  => 'nullable|string',
            'enfermedades'   => 'required|array|min:1',
        ]);

        $validated['observaciones'] = $validated['observaciones'] ?? '';
        $enfermedadesIds = $validated['enfermedades'];
        unset($validated['enfermedades']);

        $consulta->update($validated);
        $consulta->enfermedades()->sync($enfermedadesIds);

        if ($consulta->receta) {
            $consulta->receta->update([
                'id_persona' => $consulta->id_persona,
                'medico_id'  => $consulta->medico_id,
            ]);
        }

        return redirect()
            ->route('admin.salud.movimientos.consultas.recetacion', $consulta)
            ->with('success', 'Consulta actualizada correctamente. Continúe con la recetación.');
    }

    private function tieneDispensaciones(Consulta $consulta): bool
    {
        if (!$consulta->receta) {
            return false;
        }

        return Dispensacion::where('receta_medica_id', $consulta->receta->id)->exists();
    }

    /**
     * Paso 2: Vista de Recetación (Crear / Editar).
     */
    public function recetacion(Consulta $consulta)
    {
        // 1. Cargar relaciones necesarias de la consulta y receta
        $consulta->load([
            'paciente',
            'medico',
            'receta.detalles.producto',
            'receta.detalles.unidad',
        ]);

        $receta = $consulta->receta;
        $tieneDatosReales = $receta !== null;

        // 2. Construir el estado inicial que utilizará Alpine.js
        $estadoInicial = [
            'fecha' => old('fecha', optional($receta?->fecha)->format('Y-m-d') ?? now()->format('Y-m-d')),
            'vigencia' => old('vigencia', optional($receta?->vigencia)->format('Y-m-d') ?? ''),
            'descripcion' => old('descripcion', $receta?->descripcion ?? ''),
            'detalles' => old('detalles', $tieneDatosReales ? $receta->detalles->map(function ($det) {
                return [
                    'producto_id' => $det->producto_id,
                    'producto_nombre' => optional($det->producto)->nombre ?? '',
                    'cantidad' => $det->cantidad,
                    'unidad_id' => $det->unidad_id,
                    'frecuencia' => $det->frecuencia,
                    'fecha_inicio'    => $det->fecha_inicio ? Carbon::parse($det->fecha_inicio)->format('Y-m-d') : '',
                    'fecha_fin'       => $det->fecha_fin ? Carbon::parse($det->fecha_fin)->format('Y-m-d') : '',
                    'observaciones' => $det->observaciones ?? '',
                ];
            })->toArray() : []),
        ];

        // 3. Cargar productos del tipo salud (2) 
        $productos = \App\Models\Producto::where('estado', true)
            ->whereHas('categoria', function ($query) {
                $query->where('tipo_producto_id', 2);
            })
            ->orderBy('nombre')
            ->get();

        $unidades = \App\Models\Unidad::all();

        // 4. Retornar la vista incluyendo $estadoInicial y $tieneDatosReales
        return view('admin.salud.movimientos.consultas.recetacion', compact(
            'consulta',
            'estadoInicial',
            'tieneDatosReales',
            'productos',
            'unidades'
        ));
    }

    /**
     * Paso 2: Guardar o Actualizar Receta Médica.
     */
    public function storeReceta(Request $request, Consulta $consulta)
    {
        $request->validate([
            'fecha' => 'required|date',
            'vigencia' => 'nullable|date|after_or_equal:fecha',
            'descripcion' => 'nullable|string|max:500',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.cantidad' => 'required|numeric|min:0.01',
            'detalles.*.unidad_id' => 'required|exists:unidades,id',
            'detalles.*.frecuencia' => 'required|string|max:255',
            'detalles.*.observaciones' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // 1. Extraer ID de la persona/paciente
            $idPersona = $consulta->id_persona
                ?? $consulta->persona_id
                ?? $consulta->paciente_id
                ?? $consulta->paciente?->id;

            // 2. Extraer ID del médico desde la consulta
            $medicoId = $consulta->medico_id
                ?? $consulta->doctor_id
                ?? $consulta->medico?->id
                ?? auth()->id();

            // 3. Crear o Actualizar la cabecera de la Receta
            $receta = $consulta->receta()->updateOrCreate(
                ['consulta_id' => $consulta->id],
                [
                    'id_persona'  => $idPersona,
                    'medico_id'   => $medicoId,
                    'fecha'       => $request->fecha,
                    'vigencia'    => $request->vigencia,
                    'descripcion' => $request->descripcion,
                    'estado'      => 1,
                ]
            );

            // 4. Limpiar ítems previos si no existen dispensaciones registradas
            $tieneDispensaciones = $receta->detalles()->whereHas('dispensaciones')->exists();

            if (!$tieneDispensaciones) {
                $receta->detalles()->delete();
            }

            // 5. Insertar los detalles
            foreach ($request->detalles as $item) {
                $unidad = \App\Models\Unidad::find($item['unidad_id']);

                $receta->detalles()->create([
                    'producto_id'        => $item['producto_id'],
                    'unidad_id'          => $item['unidad_id'],
                    'cantidad'           => 0,
                    'cantidad_prescrita' => $item['cantidad'],
                    'unidad_prescrita'   => $unidad->nombre ?? 'Unidad',
                    'equivalencia_ml'    => $item['equivalencia_ml'] ?? 0,
                    'frecuencia'         => $item['frecuencia'],
                    'fecha_inicio'       => $item['fecha_inicio'] ?? $request->fecha,
                    'fecha_fin'          => $item['fecha_fin'] ?? $request->vigencia ?? $request->fecha,
                    'observaciones'      => $item['observaciones'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.salud.movimientos.consultas.dispensacion', $consulta)
                ->with('success', 'Receta guardada correctamente. Proceda con la dispensación.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Ocurrió un error al guardar la receta: ' . $e->getMessage());
        }
    }

    /**
     * Paso 3: Formulario para dispensación de medicamentos.
     */
    public function dispensacion(Consulta $consulta)
    {
        if (!$consulta->receta) {
            return redirect()
                ->route('admin.salud.movimientos.consultas.recetacion', $consulta)
                ->with('error', 'Debe registrar la receta médica antes de acceder a la dispensación.');
        }

        $consulta->load([
            'paciente',
            'medico',
            'consultorio',
            'enfermedades',
            'receta.detalles.producto',
            'receta.detalles.unidad',
            'receta.detalles.dispensaciones.lote',
            'receta.detalles.dispensaciones.usuario.persona',
        ]);

        $sedeId = auth()->user()->persona?->sede_id ?? 1;

        // ── Calcular lotes FIFO por cada detalle pendiente ──
        $lotesPorDetalle = [];

        foreach ($consulta->receta->detalles as $detalle) {
            if ($detalle->cantidad_pendiente <= 0) continue;

            $lotes = Lote::where('producto_id', $detalle->producto_id)
                // ->where('cantidad_actual', '>', 0)   ← quitar esto
                ->where(function ($q) {
                    $q->whereNull('fecha_vencimiento')
                        ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                })
                ->whereHas('inventarioSedeLotes', function ($q) use ($sedeId) {
                    $q->where('sede_id', $sedeId)->where('cantidad', '>', 0);   // ← este sí importa
                })
                ->with(['inventarioSedeLotes' => fn($q) => $q->where('sede_id', $sedeId)])
                ->orderByRaw('fecha_vencimiento IS NULL ASC')
                ->orderBy('fecha_vencimiento', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $lotesPorDetalle[$detalle->id] = $lotes;
        }

        return view(
            'admin.salud.movimientos.consultas.dispensacion',
            compact('consulta', 'sedeId', 'lotesPorDetalle')
        );
    }

    /**
     * Paso 3 (STORE): Registra la dispensación de medicamentos y cierra la receta.
     */
    public function storeDispensacion(Request $request, Consulta $consulta)
    {
        $receta = $consulta->receta;

        if (!$receta) {
            return back()->withInput()->with('error', 'Esta consulta no tiene una receta activa.');
        }

        $request->validate([
            'items'                 => ['nullable', 'array'],
            'items.*.dispensar'     => ['nullable'],
            'items.*.cantidad'      => ['nullable', 'numeric', 'min:0'],
            'items.*.observaciones' => ['nullable', 'string'],
        ]);

        $sedeId = auth()->user()->persona?->sede_id ?? 1;
        $creados = 0;

        try {
            DB::transaction(function () use ($request, $consulta, $receta, $sedeId, &$creados) {

                $detalles = DetalleRecetasMedica::where('receta_id', $receta->id)
                    ->lockForUpdate()
                    ->get();

                foreach ($detalles as $detalle) {

                    $itemData      = $request->input("items.{$detalle->id}", []);
                    $debeDispensar = filter_var($itemData['dispensar'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    $cantidadInput = (float) ($itemData['cantidad'] ?? 0);

                    if (!$debeDispensar || $cantidadInput <= 0) {
                        continue;
                    }

                    $pendiente = $detalle->cantidad_pendiente;
                    $cantidadRestante = min($cantidadInput, $pendiente);

                    if ($cantidadRestante <= 0) continue;

                    $lotes = Lote::where('producto_id', $detalle->producto_id)
                        ->where('cantidad_actual', '>', 0)
                        ->where(function ($q) {
                            $q->whereNull('fecha_vencimiento')
                                ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                        })
                        ->whereHas('inventarioSedeLotes', function ($q) use ($sedeId) {
                            $q->where('sede_id', $sedeId)->where('cantidad', '>', 0);
                        })
                        ->orderByRaw('fecha_vencimiento IS NULL ASC')
                        ->orderBy('fecha_vencimiento', 'asc')
                        ->orderBy('id', 'asc')
                        ->lockForUpdate()
                        ->get();

                    if ($lotes->isEmpty()) {
                        throw new \RuntimeException(
                            "No hay stock en tu sede (ID: {$sedeId}) para: {$detalle->producto->nombre}."
                        );
                    }

                    $unidad = Unidad::find($detalle->unidad_id);
                    $factor = (float) ($unidad->factor_a_gramo ?? 1);
                    $totalEntregadoEsteItem = 0;

                    foreach ($lotes as $lote) {
                        if ($cantidadRestante <= 0) break;

                        $inventario = InventarioSedeLote::where('lote_id', $lote->id)
                            ->where('sede_id', $sedeId)
                            ->lockForUpdate()
                            ->first();

                        if (!$inventario || (float) $inventario->cantidad <= 0) continue;

                        $stockLote = (float) $inventario->cantidad;
                        $aEntregarDeEsteLote = min($stockLote, $cantidadRestante);
                        if ($aEntregarDeEsteLote <= 0) continue;

                        $cantidadConvertida = round($aEntregarDeEsteLote * $factor, 2);

                        $cantAntesInv   = (float) $inventario->cantidad;
                        $cantDespuesInv = $cantAntesInv - $aEntregarDeEsteLote;
                        $cantAntesConv  = (float) $inventario->cantidad_convertida;
                        $cantDespuesConv = max(0, $cantAntesConv - $cantidadConvertida);

                        $inventario->update([
                            'cantidad'            => $cantDespuesInv,
                            'cantidad_convertida' => $cantDespuesConv,
                        ]);

                        $lote->update([
                            'cantidad_actual' => max(0, (float) $lote->cantidad_actual - $aEntregarDeEsteLote),
                        ]);

                        Dispensacion::create([
                            'receta_medica_id'         => $receta->id,
                            'detalle_receta_medica_id' => $detalle->id,
                            'producto_id'              => $detalle->producto_id,
                            'id_persona'               => $consulta->id_persona,
                            'lote_id'                  => $lote->id,
                            'cantidad'                 => $aEntregarDeEsteLote,
                            'unidad_id'                => $detalle->unidad_id,
                            'sede_id'                  => $sedeId,
                            'usuario_id'               => auth()->id(),
                            'fecha'                    => now()->toDateString(),
                            'observaciones'            => $itemData['observaciones'] ?? null,
                        ]);

                        MovimientoInventario::create([
                            'producto_id'         => $detalle->producto_id,
                            'lote_id'             => $lote->id,
                            'sede_id'             => $sedeId,
                            'modulo_origen_id'    => null,
                            'tipo_movimiento'     => 'SALIDA',
                            'unidad_id'           => $detalle->unidad_id,
                            'cantidad'            => $aEntregarDeEsteLote,
                            'cantidad_convertida' => $cantidadConvertida,
                            'cantidad_anterior'   => $cantAntesInv,
                            'cantidad_final'      => $cantDespuesInv,
                            'referencia_type'     => 'Dispensacion',
                            'fecha'               => now()->toDateString(),
                            'observaciones'       => 'Dispensación en consulta #' . $consulta->id
                                . ' · Lote ' . $lote->codigo_lote,
                        ]);

                        $totalEntregadoEsteItem += $aEntregarDeEsteLote;
                        $cantidadRestante -= $aEntregarDeEsteLote;
                    }

                    if ($cantidadRestante > 0) {
                        throw new \RuntimeException(
                            "Stock insuficiente en tu sede para: {$detalle->producto->nombre}. "
                                . "Faltaron {$cantidadRestante} unidades."
                        );
                    }

                    $detalle->update([
                        'cantidad' => (float) $detalle->cantidad + $totalEntregadoEsteItem,
                    ]);

                    $creados++;
                }
            });

            // ═══════════════════════════════════════════════════════════
            // FUERA de la transacción: verificar pendientes y actualizar estado
            // ═══════════════════════════════════════════════════════════
            $quedanPendientes = DetalleRecetasMedica::where('receta_id', $receta->id)
                ->get()
                ->contains(fn($d) => $d->cantidad_pendiente > 0);

            // Reglas:
            //   1) Dispensó algo + quedan pendientes → estado 1 (abierta, se puede completar después)
            //   2) Dispensó algo + no quedan pendientes → estado 2 (cerrada, todo entregado)
            //   3) No dispensó nada (checkbox vacíos) → estado 2 (cerrada sin entregas)
            $estadoFinal = ($creados > 0 && $quedanPendientes) ? 1 : 2;
            $receta->update(['estado' => $estadoFinal]);

            // Mensaje de éxito según el caso
            if ($creados === 0) {
                $mensaje = 'Atención finalizada sin entregas. La consulta ha sido cerrada.';
            } else {
                $mensaje = "Se registraron {$creados} dispensación(es) y se descontó el inventario.";
                $mensaje .= $estadoFinal === 1
                    ? ' Quedan medicamentos pendientes por entregar.'
                    : ' La atención ha sido finalizada.';
            }

            return redirect()
                ->route('admin.salud.movimientos.consultas.index')
                ->with('success', $mensaje);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error al dispensar: ' . $e->getMessage());
        }
    }

    /**
     * Histórico de la consulta.
     */
    public function show(Consulta $consulta)
    {
        $consulta->load([
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
        ]);

        return view('admin.salud.movimientos.consultas.show', compact('consulta'));
    }

    /**
     * Búsqueda AJAX de enfermedades.
     */
    public function buscarEnfermedades(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $enfermedades = Enfermedad::where('categoria', Enfermedad::TIPO_FISICA)
            ->where('activo', 1)
            ->where('nombre', 'like', "%{$q}%")
            ->orderBy('nombre')
            ->limit(15)
            ->get(['id', 'nombre', 'codigo']);

        return response()->json($enfermedades);
    }

    /**
     * Búsqueda AJAX de pacientes.
     */
    public function buscarPersonas(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $personas = Persona::where('nombre_persona', 'like', "%{$q}%")
            ->orWhere('apellido_persona', 'like', "%{$q}%")
            ->orWhere('cedula_persona', 'like', "%{$q}%")
            ->limit(10)
            ->get()
            ->map(function ($persona) {
                return [
                    'id_persona' => $persona->id_persona,
                    'nombre' => ' ' . ($persona->cedula_persona ?? 'S/C') . ' ' . ($persona->nombre_persona ?? '') . ' ' . ($persona->apellido_persona ?? '')
                ];
            });

        return response()->json($personas);
    }

    /**
     * Generar el PDF del Récipe Médico.
     */
    public function generarRecipePdf(Consulta $consulta)
    {
        ini_set('memory_limit', '256M');
        if (!$consulta->receta) {
            return redirect()
                ->back()
                ->with('error', 'Esta consulta no tiene un récipe médico registrado.');
        }

        $consulta->load([
            'paciente',
            'medico',
            'consultorio',
            'receta.detalles.producto',
            'receta.detalles.unidad'
        ]);

        $pdf = Pdf::loadView('admin.salud.movimientos.consultas.recipe', compact('consulta'));

        $pdf->setPaper('a5', 'portrait');

        $nombreArchivo = 'recipe-' . $consulta->paciente->cedula_persona . '-' . $consulta->fecha->format('Y-m-d') . '.pdf';

        return $pdf->stream($nombreArchivo);
    }

    public function estadisticas(Request $request, SaludHomeService $service)
    {
        $reportType = $request->input('report_type', 'completo');
        $periodo    = $request->input('periodo', 'mensual');
        $data = $service->getDashboardData($request->all());
        $format = $request->input('format', 'json');

        if ($format === 'json') {
            return response()->json([
                'consultas'   => $data['consultas'],
                'resumen'     => $data['resumen'],
                'fechaInicio' => $data['fechaInicio'],
                'fechaFin'    => $data['fechaFin'],
            ]);
        }

        if ($format === 'pdf') {
            ini_set('memory_limit', '512M');

            // ── Resolver etiquetas legibles de los filtros aplicados ──
            $consultorioId = $request->input('consultorio_id');
            $medicoId      = $request->input('medico_id');

            $consultorioNombre = $consultorioId
                ? Consultorio::find($consultorioId)?->nombre
                : null;

            $medicoNombre = null;
            if ($medicoId) {
                $med = \App\Models\Persona::find($medicoId);
                if ($med) {
                    $medicoNombre = trim(($med->nombre_persona ?? '') . ' ' . ($med->apellido_persona ?? ''));
                }
            }

            $enfermedadIds = (array) $request->input('enfermedad_ids', []);
            $enfermedadIds = array_filter(array_map('intval', $enfermedadIds));
            $enfermedadesNombres = !empty($enfermedadIds)
                ? Enfermedad::whereIn('id', $enfermedadIds)->pluck('nombre')->toArray()
                : [];

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.salud.pdf', [
                // Datos base
                'consultas'    => $data['consultas'],
                'resumen'      => $data['resumen'],
                'fechaInicio'  => $data['fechaInicio'],
                'fechaFin'     => $data['fechaFin'],
                'periodo'      => $periodo,
                'reportType'   => $reportType,

                // Filtros aplicados (con etiquetas legibles)
                'estado_receta'        => $request->input('estado_receta'),
                'consultorio_nombre'   => $consultorioNombre,
                'medico_nombre'        => $medicoNombre,
                'enfermedades_nombres' => $enfermedadesNombres,
                'perfil_academico'     => $request->input('perfil_academico'),
                'pnf'                  => $request->input('pnf'),
            ]);

            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isRemoteEnabled'      => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont'          => 'DejaVu Sans',
            ]);

            $nombreArchivo = 'reporte-salud-' . $reportType . '-' . now()->format('Ymd_His') . '.pdf';
            return $pdf->stream($nombreArchivo);
        }

        if ($format === 'word') {
            $consultorioId = $request->input('consultorio_id');
            $medicoId      = $request->input('medico_id');

            $consultorioNombre = $consultorioId
                ? Consultorio::find($consultorioId)?->nombre
                : null;

            $medicoNombre = null;
            if ($medicoId) {
                $med = \App\Models\Persona::find($medicoId);
                if ($med) {
                    $medicoNombre = trim(($med->nombre_persona ?? '') . ' ' . ($med->apellido_persona ?? ''));
                }
            }

            $enfermedadIds = (array) $request->input('enfermedad_ids', []);
            $enfermedadIds = array_filter(array_map('intval', $enfermedadIds));
            $enfermedadesNombres = !empty($enfermedadIds)
                ? Enfermedad::whereIn('id', $enfermedadIds)->pluck('nombre')->toArray()
                : [];

            $tempFile = \App\Exports\Salud\SaludEstadisticasWordExport::generate(
                $data['consultas'],
                $data['resumen'],
                $data['fechaInicio'],
                $data['fechaFin'],
                $periodo,
                $reportType,
                [
                    'estado_receta'        => $request->input('estado_receta'),
                    'consultorio_nombre'   => $consultorioNombre,
                    'medico_nombre'        => $medicoNombre,
                    'enfermedades_nombres' => $enfermedadesNombres,
                    'perfil_academico'     => $request->input('perfil_academico'),
                    'pnf'                  => $request->input('pnf'),
                ]
            );

            return response()
                ->download($tempFile, 'Estadisticas_Salud_' . now()->format('Ymd_His') . '.docx')
                ->deleteFileAfterSend(true);
        }

        abort(400, 'Formato no soportado.');
    }
}
