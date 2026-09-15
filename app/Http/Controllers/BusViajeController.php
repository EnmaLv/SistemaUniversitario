<?php

namespace App\Http\Controllers;

use App\Models\BusRuta;
use App\Models\BusVehiculo;
use App\Models\BusViaje;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BusViajeController extends Controller
{
    public function index(Request $request)
    {
        $query = BusViaje::with([
            'vehiculo',
            'ruta',
            'conductor.persona'
        ]);

        if ($request->filled('estado') && $request->estado !== 'todos') {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->whereHas('vehiculo', function ($v) use ($buscar) {
                    $v->where('placa', 'like', "%{$buscar}%");
                })
                    ->orWhereHas('ruta', function ($r) use ($buscar) {
                        $r->where('nombre', 'like', "%{$buscar}%");
                    })
                    ->orWhereHas('conductor.persona', function ($p) use ($buscar) {
                        $p->where('nombre_persona', 'like', "%{$buscar}%")
                            ->orWhere('apellido_persona', 'like', "%{$buscar}%");
                    });
            });
        }

        $viajes = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends($request->all());

        return view('admin.transporte.maestros.bus_viajes.index', compact('viajes'));
    }

    public function create()
    {
        $vehiculosOcupados = BusViaje::whereIn('estado', ['programado', 'en_curso'])
            ->pluck('vehiculo_id');

        $vehiculos = BusVehiculo::where('estado', 1)
            ->whereNotIn('id', $vehiculosOcupados)
            ->get();

        $rutas = BusRuta::where('estado', 1)->get();
        $conductoresOcupados = BusViaje::whereIn('estado', ['programado', 'en_curso'])
            ->whereNotNull('conductor_id')
            ->pluck('conductor_id');

        $conductores = Usuario::with('persona')
            ->whereNotIn('id_usuario', $conductoresOcupados)
            ->get();

        $turnoSugerido = BusViaje::calcularTurnoActual();

        return view('admin.transporte.maestros.bus_viajes.create', compact('vehiculos', 'rutas', 'conductores', 'turnoSugerido'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehiculo_id'  => ['required', 'exists:vehiculos,id'],
            'bus_ruta_id'  => ['required', 'exists:bus_rutas,id'],
            'conductor_id' => ['required', 'exists:usuario,id_usuario'],
            'turno'        => ['required', Rule::in(['mañana', 'tarde', 'noche'])],
        ], [
            'vehiculo_id.required'  => 'Debe seleccionar un autobús.',
            'bus_ruta_id.required'  => 'Debe seleccionar una ruta de transporte.',
            'conductor_id.required' => 'Debe asignar un conductor al viaje.',
            'turno.required'        => 'Debe especificar el turno del viaje.',
        ]);

        $vehiculoEnUso = BusViaje::where('vehiculo_id', $request->vehiculo_id)
            ->whereIn('estado', ['programado', 'en_curso'])
            ->exists();

        if ($vehiculoEnUso) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'El vehículo seleccionado ya cuenta con un viaje activo o programado.');
        }

        $conductorEnUso = BusViaje::where('conductor_id', $request->conductor_id)
            ->whereIn('estado', ['programado', 'en_curso'])
            ->exists();

        if ($conductorEnUso) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'El conductor seleccionado ya tiene un viaje activo o programado asignado.');
        }

        $viaje = BusViaje::create([
            'vehiculo_id'  => $request->vehiculo_id,
            'bus_ruta_id'  => $request->bus_ruta_id,
            'conductor_id' => $request->conductor_id,
            'turno'        => $request->turno,
            'estado'       => 'programado',
            'km_inicio'    => 0,
            'km_fin'       => 0,
            'distancia_km' => 0,
            'litros_gastados' => 0,
            'pasajeros'    => 0,
            'hubo_desvio'  => false,
        ]);

        $viaje->update([
            'firebase_id' => 'viaje_' . $viaje->id,
        ]);

        return redirect()->route('admin.transporte.maestros.bus_viajes.index')
            ->with('success', 'El viaje ha sido programado y asignado exitosamente.');
    }

    public function edit(BusViaje $busViaje)
    {
        $vehiculosOcupados = BusViaje::where('id', '!=', $busViaje->id)
            ->whereIn('estado', ['programado', 'en_curso'])
            ->pluck('vehiculo_id');

        $vehiculos = BusVehiculo::where('estado', 1)
            ->whereNotIn('id', $vehiculosOcupados)
            ->get();

        $rutas = BusRuta::where('estado', 1)->get();

        $conductoresOcupados = BusViaje::where('id', '!=', $busViaje->id)
            ->whereIn('estado', ['programado', 'en_curso'])
            ->whereNotNull('conductor_id')
            ->pluck('conductor_id');

        $conductores = Usuario::with('persona')
            ->whereNotIn('id_usuario', $conductoresOcupados)
            ->get();

        return view('admin.transporte.maestros.bus_viajes.edit', compact('busViaje', 'vehiculos', 'rutas', 'conductores'));
    }

    public function update(Request $request, BusViaje $busViaje)
    {
        $request->validate([
            'vehiculo_id'  => ['required', 'exists:vehiculos,id'],
            'bus_ruta_id'  => ['required', 'exists:bus_rutas,id'],
            'conductor_id' => ['required', 'exists:usuario,id_usuario'],
            'turno'        => ['required', Rule::in(['mañana', 'tarde', 'noche'])],
        ], [
            'vehiculo_id.required'  => 'Debe seleccionar un autobús.',
            'bus_ruta_id.required'  => 'Debe seleccionar una ruta de transporte.',
            'conductor_id.required' => 'Debe asignar un conductor al viaje.',
            'turno.required'        => 'Debe especificar el turno del viaje.',
        ]);

        $vehiculoEnUso = BusViaje::where('id', '!=', $busViaje->id)
            ->where('vehiculo_id', $request->vehiculo_id)
            ->whereIn('estado', ['programado', 'en_curso'])
            ->exists();

        if ($vehiculoEnUso) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'El vehículo seleccionado ya cuenta con otro viaje activo o programado.');
        }

        $conductorEnUso = BusViaje::where('id', '!=', $busViaje->id)
            ->where('conductor_id', $request->conductor_id)
            ->whereIn('estado', ['programado', 'en_curso'])
            ->exists();

        if ($conductorEnUso) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'El conductor seleccionado ya tiene otro viaje activo o programado asignado.');
        }

        $busViaje->update([
            'vehiculo_id'  => $request->vehiculo_id,
            'bus_ruta_id'  => $request->bus_ruta_id,
            'conductor_id' => $request->conductor_id,
            'turno'        => $request->turno,
        ]);

        return redirect()->route('admin.transporte.maestros.bus_viajes.index')
            ->with('success', 'El viaje se ha actualizado exitosamente.');
    }

    public function show(BusViaje $busViaje)
    {
        $busViaje->load([
            'vehiculo',
            'ruta.paradas' => function ($query) {
                $query->orderBy('orden', 'asc');
            },
            'conductor.persona'
        ]);

        return view('admin.transporte.maestros.bus_viajes.show', compact('busViaje'));
    }

    public function destroy(BusViaje $busViaje)
    {
        $busViaje->update([
            'estado' => 'inactivo'
        ]);

        return redirect()->route('admin.transporte.maestros.bus_viajes.index')->with('success', 'El viaje ha sido desactivado correctamente.');
    }

    public function cancelar(Request $request, BusViaje $busViaje)
    {
        $request->validate([
            'motivo_cancelacion' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'motivo_cancelacion.required' => 'Debe ingresar una razón para cancelar el viaje.',
            'motivo_cancelacion.min'      => 'La razón debe contener al menos 5 caracteres.',
        ]);

        $busViaje->update([
            'estado'             => 'cancelado',
            'motivo_cancelacion' => $request->motivo_cancelacion,
        ]);

        return redirect()->route('admin.transporte.maestros.bus_viajes.index')->with('success', 'El viaje ha sido cancelado exitosamente.');
    }

    public function gpsLogs(BusViaje $busViaje)
    {
        $logs = $busViaje->gpsLogs()
            ->orderBy('id')
            ->get(['lat', 'lng', 'velocidad', 'heading', 'created_at']);

        return response()->json([
            'success' => true,
            'data'    => $logs,
        ]);
    }
}