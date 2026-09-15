<?php

namespace App\Http\Controllers\salud;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use App\Models\salud\Consulta;
use App\Models\salud\Consultorio;
use App\Models\salud\DetalleRecetasMedica;
use App\Models\salud\Dispensacion;
use App\Models\salud\Enfermedad;
use App\Models\salud\HorarioConsultorio;
use App\Models\Lote;
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
            $request->input('fecha_desde'),
            $request->input('fecha_hasta'),
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

            // 5. Insertar los detalles asegurando fecha_inicio y fecha_fin
            foreach ($request->detalles as $item) {
                $receta->detalles()->create([
                    'producto_id'   => $item['producto_id'],
                    'cantidad'      => $item['cantidad'],
                    'unidad_id'     => $item['unidad_id'],
                    'frecuencia'    => $item['frecuencia'],
                    'fecha_inicio'  => $item['fecha_inicio'] ?? $request->fecha,
                    'fecha_fin'     => $item['fecha_fin'] ?? $request->vigencia ?? $request->fecha,
                    'observaciones' => $item['observaciones'] ?? null,
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

        $lotesPorProducto = [];

        foreach ($consulta->receta->detalles as $detalle) {
            if ($detalle->cantidad_pendiente > 0) {
                $lotesPorProducto[$detalle->producto_id] = Lote::disponiblesParaProducto($detalle->producto_id);
            }
        }

        return view('admin.salud.movimientos.consultas.dispensacion', compact('consulta', 'lotesPorProducto'));
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
            'items.*.lote_id'       => ['nullable', 'exists:lotes,id'],
            'items.*.cantidad'      => ['nullable', 'numeric', 'min:0'],
            'items.*.observaciones' => ['nullable', 'string'],
        ]);

        $creados = 0;
        $sede = 1; // Sede Acarigua por defecto

        // Obtenemos los items
        $itemsRequest = $request->input('items', []);

        try {
            DB::transaction(function () use ($itemsRequest, $consulta, $receta, &$creados, $sede) {
                $detallesActuales = DetalleRecetasMedica::where('receta_id', $receta->id)->get();

                foreach ($detallesActuales as $detalle) {
                    // Accedemos al array
                    $itemData = $itemsRequest[$detalle->id] ?? null;

                    $debeDispensar = $itemData ? filter_var($itemData['dispensar'] ?? false, FILTER_VALIDATE_BOOLEAN) : false;
                    $cantidadInput = ($itemData && isset($itemData['cantidad'])) ? (float) $itemData['cantidad'] : 0;

                    if ($debeDispensar && $cantidadInput > 0) {
                        $pendiente = $detalle->cantidad_pendiente;
                        $cantidadAEntregar = min($cantidadInput, $pendiente);

                        Dispensacion::create([
                            'receta_medica_id'         => $receta->id,
                            'detalle_receta_medica_id' => $detalle->id,
                            'producto_id'              => $detalle->producto_id,
                            'id_persona'               => $consulta->id_persona,
                            'lote_id'                  => $itemData['lote_id'] ?? null,
                            'cantidad'                 => $cantidadAEntregar,
                            'unidad_id'                => $detalle->unidad_id,
                            'sede_id'                  => $sede,
                            'usuario_id'               => auth()->id(),
                            'fecha'                    => now()->toDateString(),
                            'observaciones'            => $itemData['observaciones'] ?? null,
                        ]);

                        // Se iguala la cantidad del detalle a lo que realmente se entregó
                        $detalle->update(['cantidad' => $cantidadAEntregar]);
                        $creados++;
                    } else {
                        // Si no se dispensó el medicamento
                        $totalEntregado = $detalle->dispensaciones()->sum('cantidad');
                        $detalle->update(['cantidad' => $totalEntregado]);
                    }
                }

                // Marcar el estado de la receta como Finalizada / Procesada 
                $receta->update(['estado' => 2]);
            });

            return redirect()
                ->route('admin.salud.movimientos.consultas.index')
                ->with('success', $creados > 0
                    ? "Se registraron {$creados} dispensación(es) y se finalizó la atención correctamente."
                    : 'Atención finalizada sin entregas de medicamentos. La receta ha sido cerrada.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Ocurrió un error al guardar: ' . $e->getMessage());
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
}
