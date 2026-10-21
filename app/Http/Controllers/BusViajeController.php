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
            $query->where('estado_viaje_vjt', $request->estado);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->whereHas('vehiculo', function ($v) use ($buscar) {
                    $v->where('placa_vtr', 'like', "%{$buscar}%");
                })
                    ->orWhereHas('ruta', function ($r) use ($buscar) {
                        $r->where('nombre_rtr', 'like', "%{$buscar}%");
                    })
                    ->orWhereHas('conductor.persona', function ($p) use ($buscar) {
                        $p->where('nombres_per', 'like', "%{$buscar}%")
                            ->orWhere('apellidos_per', 'like', "%{$buscar}%");
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
        $vehiculosOcupados = BusViaje::whereIn('estado_viaje_vjt', ['programado', 'en_curso'])
            ->pluck('vtr_id');

        $vehiculos = BusVehiculo::activos()
            ->where('estado_operativo_vtr', 'disponible')
            ->whereNotIn('id_vtr', $vehiculosOcupados)
            ->get();

        $rutas = BusRuta::activos()->with(['horarios', 'paradas'])->get();
        $conductoresOcupados = BusViaje::whereIn('estado_viaje_vjt', ['programado', 'en_curso'])
            ->whereNotNull('conductor_use_id')
            ->pluck('conductor_use_id');

        $conductores = Usuario::with('persona')
            ->whereNotIn('id_use', $conductoresOcupados)
            ->get();

        $turnoSugerido = BusViaje::calcularTurnoActual();

        return view('admin.transporte.maestros.bus_viajes.create',
            compact('vehiculos', 'rutas', 'conductores', 'turnoSugerido'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehiculo_id'  => ['required', 'exists:vehiculo_transporte,id_vtr'],
            'bus_ruta_id'  => ['required', 'exists:ruta_transporte,id_rtr'],
            'conductor_id' => ['required', 'exists:user,id_use'],
            'turno'        => ['required', Rule::in(['mañana', 'tarde', 'noche'])],
        ], [
            'vehiculo_id.required'  => 'Debe seleccionar un autobús.',
            'bus_ruta_id.required'  => 'Debe seleccionar una ruta de transporte.',
            'conductor_id.required' => 'Debe asignar un conductor al viaje.',
            'turno.required'        => 'Debe especificar el turno del viaje.',
        ]);

        $vehiculoEnUso = BusViaje::where('vtr_id', $request->vehiculo_id)
            ->whereIn('estado_viaje_vjt', ['programado', 'en_curso'])
            ->exists();

        if ($vehiculoEnUso) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'El vehículo seleccionado ya cuenta con un viaje activo o programado.');
        }

        $conductorEnUso = BusViaje::where('conductor_use_id', $request->conductor_id)
            ->whereIn('estado_viaje_vjt', ['programado', 'en_curso'])
            ->exists();

        if ($conductorEnUso) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'El conductor seleccionado ya tiene un viaje activo o programado asignado.');
        }

        $viaje = BusViaje::create([
            'vtr_id'              => $request->vehiculo_id,
            'rtr_id'              => $request->bus_ruta_id,
            'conductor_use_id'    => $request->conductor_id,
            'turno_vjt'           => $request->turno,
            'estado_viaje_vjt'    => 'programado',
            'km_inicio_vjt'       => 0,
            'km_fin_vjt'          => 0,
            'distancia_km_vjt'    => 0,
            'litros_gastados_vjt' => 0,
            'total_pasajeros_vjt' => 0,
            'hubo_desvio_vjt'     => false,
        ]);

        $viaje->update([
            'firebase_id_vjt' => 'viaje_' . $viaje->id_vjt,
        ]);

        return redirect()->route('admin.transporte.maestros.bus_viajes.index')
            ->with('success', 'El viaje ha sido programado y asignado exitosamente.');
    }

    public function edit(BusViaje $busViaje)
    {
        $vehiculosOcupados = BusViaje::where('id_vjt', '!=', $busViaje->id_vjt)
            ->whereIn('estado_viaje_vjt', ['programado', 'en_curso'])
            ->pluck('vtr_id');

        $vehiculos = BusVehiculo::activos()
            ->where(fn ($q) => $q->where('estado_operativo_vtr', 'disponible')->orWhere('id_vtr', $busViaje->vtr_id))
            ->whereNotIn('id_vtr', $vehiculosOcupados)
            ->get();

        $rutas = BusRuta::activos()->with(['horarios', 'paradas'])->get();

        $conductoresOcupados = BusViaje::where('id_vjt', '!=', $busViaje->id_vjt)
            ->whereIn('estado_viaje_vjt', ['programado', 'en_curso'])
            ->whereNotNull('conductor_use_id')
            ->pluck('conductor_use_id');

        $conductores = Usuario::with('persona')
            ->whereNotIn('id_use', $conductoresOcupados)
            ->get();

        return view('admin.transporte.maestros.bus_viajes.edit',
            compact('busViaje', 'vehiculos', 'rutas', 'conductores'));
    }

    public function update(Request $request, BusViaje $busViaje)
    {
        $request->validate([
            'vehiculo_id'  => ['required', 'exists:vehiculo_transporte,id_vtr'],
            'bus_ruta_id'  => ['required', 'exists:ruta_transporte,id_rtr'],
            'conductor_id' => ['required', 'exists:user,id_use'],
            'turno'        => ['required', Rule::in(['mañana', 'tarde', 'noche'])],
        ], [
            'vehiculo_id.required'  => 'Debe seleccionar un autobús.',
            'bus_ruta_id.required'  => 'Debe seleccionar una ruta de transporte.',
            'conductor_id.required' => 'Debe asignar un conductor al viaje.',
            'turno.required'        => 'Debe especificar el turno del viaje.',
        ]);

        $vehiculoEnUso = BusViaje::where('id_vjt', '!=', $busViaje->id_vjt)
            ->where('vtr_id', $request->vehiculo_id)
            ->whereIn('estado_viaje_vjt', ['programado', 'en_curso'])
            ->exists();

        if ($vehiculoEnUso) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'El vehículo seleccionado ya cuenta con otro viaje activo o programado.');
        }

        $conductorEnUso = BusViaje::where('id_vjt', '!=', $busViaje->id_vjt)
            ->where('conductor_use_id', $request->conductor_id)
            ->whereIn('estado_viaje_vjt', ['programado', 'en_curso'])
            ->exists();

        if ($conductorEnUso) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'El conductor seleccionado ya tiene otro viaje activo o programado asignado.');
        }

        $busViaje->update([
            'vtr_id'           => $request->vehiculo_id,
            'rtr_id'           => $request->bus_ruta_id,
            'conductor_use_id' => $request->conductor_id,
            'turno_vjt'        => $request->turno,
        ]);

        return redirect()->route('admin.transporte.maestros.bus_viajes.index')
            ->with('success', 'El viaje se ha actualizado exitosamente.');
    }

    public function posicion(BusViaje $busViaje)
    {
        return app(\App\Http\Controllers\Api\BusViajeApiController::class)->obtenerPosicion($busViaje);
    }

    public function show(BusViaje $busViaje)
    {
        $busViaje->load([
            'vehiculo',
            'ruta.paradas' => function ($query) {
                $query->orderBy('ruta_parada_transporte.orden_rpt', 'asc');
            },
            'conductor.persona'
        ]);

        return view('admin.transporte.maestros.bus_viajes.show', compact('busViaje'));
    }

    public function destroy(BusViaje $busViaje)
    {
        $busViaje->update([
            'estado_viaje_vjt' => 'inactivo',
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
            'estado_viaje_vjt' => 'cancelado',
        ]);

        return redirect()->route('admin.transporte.maestros.bus_viajes.index')->with('success', 'El viaje ha sido cancelado exitosamente.');
    }

    public function gpsLogs(BusViaje $busViaje)
    {
        $logs = $busViaje->gpsLogs()
            ->orderBy('id_lgt')
            ->get(['lat_lgt', 'lng_lgt', 'velocidad_lgt', 'heading_lgt', 'created_at'])
            ->map(fn ($log) => [
                'lat'        => $log->lat_lgt,
                'lng'        => $log->lng_lgt,
                'velocidad'  => $log->velocidad_lgt,
                'heading'    => $log->heading_lgt,
                'created_at' => $log->created_at,
            ]);

        return response()->json([
            'success' => true,
            'data'    => $logs,
        ]);
    }
}
