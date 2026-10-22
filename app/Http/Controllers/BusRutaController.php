<?php

namespace App\Http\Controllers;

use App\Models\BusRuta;
use App\Models\Sede;
use App\Models\BusParada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusRutaController extends Controller
{
    private function rules(?int $excludeId = null): array
    {
        return [
            'nombre'                => 'required|string|max:100|unique:ruta_transporte,nombre_rtr,' . ($excludeId ?? 'NULL') . ',id_rtr',
            'distancia_km'          => 'required|numeric|min:0.1|max:9999',
            'descripcion'           => 'nullable|string|max:1000',
            'sed_id'               => 'required|exists:sede,id_sed',
            'horarios'              => 'required|array|min:1',
            'horarios.*.hora_salida' => 'required|date_format:H:i',
            'horarios.*.tipo_viaje'  => 'required|in:entrada,salida',
            'paradas'               => 'required|array|min:2',
            'paradas.*'             => 'exists:parada_transporte,id_ptr'
        ];
    }

    public function index(Request $request)
    {
        $rutas = BusRuta::listarRutas($request->buscar, $request->input('estado', 1));
        return view('admin.transporte.maestros.bus_rutas.index', compact('rutas'));
    }

    public function create()
    {
        $sedes = Sede::orderBy('nombre_sed')->get();
        $paradas = $this->paradasParaMapa(BusParada::activos());
        return view('admin.transporte.maestros.bus_rutas.create', compact('sedes', 'paradas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        DB::beginTransaction();
        try {
            $ruta = BusRuta::crearRuta($validated);

            foreach ($validated['horarios'] as $item) {
                $ruta->horarios()->create([
                    'hora_salida_hrt' => $item['hora_salida'],
                    'tipo_viaje_hrt'  => $item['tipo_viaje'],
                    'status_id'       => BusRuta::STATUS_ACTIVO,
                ]);
            }

            foreach ($validated['paradas'] as $index => $paradaId) {
                $ruta->paradas()->attach($paradaId, ['orden_rpt' => $index + 1]);
            }

            DB::commit();
            return redirect()
                ->route('admin.transporte.maestros.bus_rutas.index')
                ->with('success', 'Ruta guardada con su mapa de paradas e itinerarios.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->withErrors(['error' => 'Error al procesar: ' . \App\Support\Seguridad\MensajeError::de($e)]);
        }
    }

    public function edit(BusRuta $busRuta)
    {
        $busRuta->load(['horarios' => fn($q) => $q->orderBy('hora_salida_hrt'), 'paradas']);
        $sedes = Sede::orderBy('nombre_sed')->get();
        $paradas = $this->paradasParaMapa(BusParada::query());
        return view('admin.transporte.maestros.bus_rutas.edit', compact('busRuta', 'sedes', 'paradas'));
    }

    public function update(Request $request, BusRuta $busRuta)
    {
        $validated = $request->validate($this->rules($busRuta->id_rtr));

        DB::beginTransaction();
        try {
            BusRuta::actualizarRuta($busRuta, $validated);

            $busRuta->horarios()->delete();
            foreach ($validated['horarios'] as $item) {
                $busRuta->horarios()->create([
                    'hora_salida_hrt' => $item['hora_salida'],
                    'tipo_viaje_hrt'  => $item['tipo_viaje'],
                    'status_id'       => BusRuta::STATUS_ACTIVO,
                ]);
            }

            $busRuta->paradas()->detach();
            foreach ($validated['paradas'] as $index => $paradaId) {
                $busRuta->paradas()->attach($paradaId, ['orden_rpt' => $index + 1]);
            }

            DB::commit();
            return redirect()
                ->route('admin.transporte.maestros.bus_rutas.index')
                ->with('success', 'Ruta visual modificada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->withErrors(['error' => 'Error al actualizar: ' . \App\Support\Seguridad\MensajeError::de($e)]);
        }
    }

    public function destroy(BusRuta $busRuta)
    {
        $busRuta->update(['status_id' => BusRuta::STATUS_INACTIVO]);
        return redirect()->route('admin.transporte.maestros.bus_rutas.index')->with('success', 'Inactivada.');
    }

    public function activar(BusRuta $busRuta)
    {
        $busRuta->update(['status_id' => BusRuta::STATUS_ACTIVO]);
        return redirect()->route('admin.transporte.maestros.bus_rutas.index')->with('success', 'Activada.');
    }

    public function verificarNombre(Request $request)
    {
        $query = BusRuta::where('nombre_rtr', trim($request->nombre));
        if ($request->exclude) {
            $query->where('id_rtr', '!=', $request->exclude);
        }
        return response()->json(['existe' => $query->exists()]);
    }

    private function paradasParaMapa($consulta)
    {
        return $consulta->orderBy('nombre_ptr')
            ->get(['id_ptr', 'nombre_ptr', 'lat_ptr', 'lng_ptr'])
            ->map(fn ($parada) => [
                'id'     => $parada->id_ptr,
                'nombre' => $parada->nombre_ptr,
                'lat'    => $parada->lat_ptr,
                'lng'    => $parada->lng_ptr,
            ]);
    }
}