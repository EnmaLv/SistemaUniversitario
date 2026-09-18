<?php

namespace App\Http\Controllers;

use App\Models\BusRuta;
use App\Models\BusVehiculo;
use App\Models\BusViaje;
use App\Models\BusGpsLog;
use App\Models\Usuario;
use App\Services\Transporte\CalculoCombustibleService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BusViajeController extends Controller
{
    protected CalculoCombustibleService $calculoCombustible;

    public function __construct(CalculoCombustibleService $calculoCombustible)
    {
        $this->calculoCombustible = $calculoCombustible;
    }

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

    public function storeGps(Request $request, BusViaje $busViaje)
    {
        if ($busViaje->estado !== 'en_curso') {
            return response()->json([
                'success' => false,
                'message' => 'El viaje no está en curso; no se aceptan puntos GPS.',
            ], 409);
        }

        $validado = $request->validate([
            'local_id'  => ['nullable', 'string', 'max:64'],
            'lat'       => ['required', 'numeric', 'between:-90,90'],
            'lng'       => ['required', 'numeric', 'between:-180,180'],
            'velocidad' => ['nullable', 'numeric', 'min:0'],
            'heading'   => ['nullable', 'numeric'],
            'timestamp' => ['nullable', 'date'],
            'origen'    => ['nullable', 'string', 'max:50'],
        ]);

        $registradoEn = $validado['timestamp'] ?? now();

        if (!empty($validado['local_id'])) {
            $yaExiste = BusGpsLog::where('local_id', $validado['local_id'])->exists();
            if ($yaExiste) {
                return response()->json(['success' => true, 'message' => 'Punto ya registrado.']);
            }
        }

        BusGpsLog::create([
            'local_id'      => $validado['local_id'] ?? null,
            'bus_viaje_id'  => $busViaje->id,
            'lat'           => $validado['lat'],
            'lng'           => $validado['lng'],
            'velocidad'     => $validado['velocidad'] ?? 0,
            'heading'       => $validado['heading'] ?? null,
            'registrado_en' => $registradoEn,
            'origen'        => $validado['origen'] ?? config('transporte.origen_gps_por_defecto', 'app_movil'),
        ]);

        return response()->json(['success' => true]);
    }

    public function iniciar(Request $request, BusViaje $busViaje)
    {
        if ($busViaje->estado !== 'programado') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se puede iniciar un viaje que esté en estado "programado".',
            ], 409);
        }

        $request->validate([
            'pasajeros' => ['nullable', 'integer', 'min:0'],
        ]);

        $vehiculo = $busViaje->vehiculo;

        $busViaje->update([
            'estado'       => 'en_curso',
            'fecha_inicio' => now(),
            'km_inicio'    => $vehiculo->km_actual,
            'pasajeros'    => $request->input('pasajeros', $busViaje->pasajeros),
        ]);

        $vehiculo->update(['estado' => 'en_ruta']);

        return response()->json([
            'success' => true,
            'message' => 'Viaje iniciado.',
            'data'    => $busViaje->fresh(),
        ]);
    }

    public function finalizar(Request $request, BusViaje $busViaje)
    {
        if ($busViaje->estado !== 'en_curso') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se puede finalizar un viaje que esté "en_curso".',
            ], 409);
        }

        $request->validate([
            'hubo_desvio'    => ['nullable', 'boolean'],
            'motivo_desvio'  => ['nullable', 'string', 'max:100', 'required_if:hubo_desvio,true'],
        ]);

        $busViaje->loadMissing('vehiculo', 'ruta');

        $resultado = $this->calculoCombustible->calcularParaViaje($busViaje);

        $this->calculoCombustible->aplicarYGuardar($busViaje, $resultado);

        $busViaje->update([
            'estado'         => 'finalizado',
            'hubo_desvio'    => $request->boolean('hubo_desvio'),
            'motivo_desvio'  => $request->input('motivo_desvio'),
        ]);

        $busViaje->vehiculo->update(['estado' => 'disponible']);

        return response()->json([
            'success' => true,
            'message' => 'Viaje finalizado y combustible calculado.',
            'data'    => array_merge($resultado, [
                'viaje'    => $busViaje->fresh(),
                'vehiculo' => $busViaje->vehiculo->fresh(),
            ]),
        ]);
    }
}