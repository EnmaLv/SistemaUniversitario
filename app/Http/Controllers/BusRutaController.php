<?php

namespace App\Http\Controllers;

use App\Models\BusRuta;
use App\Models\Sede;
use App\Models\BusParada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusRutaController extends Controller
{
    private function rules(int $excludeId = null): array
    {
        return [
            'nombre'                => 'required|string|max:100|unique:bus_rutas,nombre,' . $excludeId,
            'distancia_km'          => 'required|numeric|min:0.1|max:9999',
            'descripcion'           => 'nullable|string|max:1000',
            'sede_id'               => 'required|exists:sede,id',
            'horarios'              => 'required|array|min:1',
            'horarios.*.hora_salida' => 'required|date_format:H:i',
            'horarios.*.tipo_viaje'  => 'required|in:entrada,salida',
            'paradas'               => 'required|array|min:2',
            'paradas.*'             => 'exists:bus_paradas,id'
        ];
    }

    public function index(Request $request)
    {
        $rutas = BusRuta::listarRutas($request->buscar, $request->input('estado', 1));
        return view('admin.transporte.maestros.bus_rutas.index', compact('rutas'));
    }

    public function create()
    {
        $sedes = Sede::orderBy('nombre')->get();
        $paradas = BusParada::where('estado', 1)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'lat', 'lng']);
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
                    'hora_salida' => $item['hora_salida'],
                    'tipo_viaje'  => $item['tipo_viaje'],
                    'estado'      => 1
                ]);
            }

            foreach ($validated['paradas'] as $index => $paradaId) {
                $ruta->paradas()->attach($paradaId, ['orden' => $index + 1]);
            }

            DB::commit();
            return redirect()
                ->route('admin.transporte.maestros.bus_rutas.index')
                ->with('success', 'Ruta guardada con su mapa de paradas e itinerarios.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->withErrors(['error' => 'Error al procesar: ' . $e->getMessage()]);
        }
    }

    public function edit(BusRuta $busRuta)
    {
        $busRuta->load(['horarios' => fn($q) => $q->orderBy('hora_salida'), 'paradas']);
        $sedes = Sede::orderBy('nombre')->get();
        $paradas = BusParada::orderBy('nombre')->get(['id', 'nombre', 'lat', 'lng']);
        return view('admin.transporte.maestros.bus_rutas.edit', compact('busRuta', 'sedes', 'paradas'));
    }

    public function update(Request $request, BusRuta $busRuta)
    {
        $validated = $request->validate($this->rules($busRuta->id));

        DB::beginTransaction();
        try {
            BusRuta::actualizarRuta($busRuta, $validated);

            $busRuta->horarios()->delete();
            foreach ($validated['horarios'] as $item) {
                $busRuta->horarios()->create([
                    'hora_salida' => $item['hora_salida'],
                    'tipo_viaje'  => $item['tipo_viaje'],
                    'estado'      => 1
                ]);
            }

            $busRuta->paradas()->detach();
            foreach ($validated['paradas'] as $index => $paradaId) {
                $busRuta->paradas()->attach($paradaId, ['orden' => $index + 1]);
            }

            DB::commit();
            return redirect()
                ->route('admin.transporte.maestros.bus_rutas.index')
                ->with('success', 'Ruta visual modificada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->withErrors(['error' => 'Error al actualizar: ' . $e->getMessage()]);
        }
    }

    public function destroy(BusRuta $busRuta)
    {
        $busRuta->update(['estado' => 0]);
        return redirect()->route('admin.transporte.maestros.bus_rutas.index')->with('success', 'Inactivada.');
    }

    public function activar(BusRuta $busRuta)
    {
        $busRuta->update(['estado' => 1]);
        return redirect()->route('admin.transporte.maestros.bus_rutas.index')->with('success', 'Activada.');
    }

    public function verificarNombre(Request $request)
    {
        $query = BusRuta::where('nombre', trim($request->nombre));
        if ($request->exclude) {
            $query->where('id', '!=', $request->exclude);
        }
        return response()->json(['existe' => $query->exists()]);
    }
}