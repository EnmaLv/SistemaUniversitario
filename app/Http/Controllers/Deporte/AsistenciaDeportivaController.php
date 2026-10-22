<?php

namespace App\Http\Controllers\Deporte;

use App\Http\Controllers\Controller;
use App\Models\Deporte\DeporteCalendario;
use App\Models\Deporte\DeporteHorario;
use App\Models\Deporte\SesionAsistencia;
use App\Support\Deportes\ServicioAsistencia;
use App\Support\Deportes\ServicioEventos;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AsistenciaDeportivaController extends Controller
{
    public const DIAS_RECIENTES = 14;

    public function index(Request $request)
    {
        $buscar = trim((string) $request->query('q', ''));
        $hoy = Carbon::today();
        $fecha = null;

        if ($request->filled('fecha')) {
            try {
                $fecha = Carbon::parse($request->query('fecha'))->startOfDay();
            } catch (\Throwable $e) {
                $fecha = null;
            }
        }

        if ($fecha && $fecha->gt($hoy)) {
            $fecha = $hoy->copy();
        }

        $eventos = $fecha
            ? ServicioEventos::listar($fecha, $fecha, $buscar)
            : ServicioEventos::listar($hoy->copy()->subDays(self::DIAS_RECIENTES), $hoy, $buscar);

        return view('admin.deportes.asistencia.index', [
            'eventos' => $eventos,
            'buscar'  => $buscar,
            'fecha'   => $fecha,
            'hoy'     => $hoy,
            'dias'    => self::DIAS_RECIENTES,
        ]);
    }

    public function store(Request $request)
    {
        $validado = $request->validate([
            'hde_id' => ['required', 'integer'],
            'fecha'  => ['required', 'date'],
        ], [
            'hde_id.required' => 'Elige el evento de la lista.',
            'fecha.required'  => 'Falta la fecha del evento.',
        ]);

        $bloque = DeporteHorario::query()->with('calendario')->findOrFail($validado['hde_id']);
        $sesion = ServicioAsistencia::abrir($bloque, $validado['fecha']);

        return redirect()->route('admin.deportes.asistencia.show', $sesion->id_sad);
    }

    public function show(SesionAsistencia $sesion)
    {
        $bloque = ServicioAsistencia::bloque($sesion);

        return view('admin.deportes.asistencia.show', [
            'sesion' => $sesion->load('cerradaPor.persona'),
            'bloque' => $bloque,
            'estado' => ServicioAsistencia::estado($sesion, $bloque),
        ]);
    }

    public function storePresentes(Request $request, SesionAsistencia $sesion)
    {
        $request->validate(['cedulas' => ['required', 'string', 'max:20000']], [
            'cedulas.required' => 'Escribe al menos una cédula.',
        ]);

        try {
            $resultados = ServicioAsistencia::marcarCedulas($sesion, $request->input('cedulas'));
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json(['mensaje' => collect($e->errors())->flatten()->first()], 422);
            }

            throw $e;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'resultados' => $resultados,
                'estado'     => ServicioAsistencia::estado($sesion->fresh()),
            ]);
        }

        return redirect()->route('admin.deportes.asistencia.show', $sesion->id_sad)->with('resultados_asistencia', $resultados);
    }

    public function storeQuitar(Request $request, SesionAsistencia $sesion)
    {
        $validado = $request->validate(['asistencia_id' => ['required', 'integer']]);

        try {
            ServicioAsistencia::quitar($sesion, (int) $validado['asistencia_id']);
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json(['mensaje' => collect($e->errors())->flatten()->first()], 422);
            }

            throw $e;
        }

        if ($request->expectsJson()) {
            return response()->json(['estado' => ServicioAsistencia::estado($sesion->fresh())]);
        }

        return redirect()->route('admin.deportes.asistencia.show', $sesion->id_sad)->with('success', 'Se quitó a la persona de la lista de presentes.');
    }

    public function storeCierre(SesionAsistencia $sesion)
    {
        $sesion = ServicioAsistencia::cerrar($sesion);

        return redirect()->route('admin.deportes.asistencia.reporte', $sesion->id_sad)
            ->with('success', 'Asistencia cerrada. Los inscritos que no se marcaron quedaron como inasistentes.');
    }

    public function reabrir(Request $request, SesionAsistencia $sesion)
    {
        $validado = $request->validate(['motivo' => ['nullable', 'string', 'max:500']]);

        ServicioAsistencia::reabrir($sesion, $validado['motivo'] ?? null);

        return redirect()->route('admin.deportes.asistencia.show', $sesion->id_sad)
            ->with('success', 'Asistencia reabierta. Corrige la lista y vuelve a cerrarla.');
    }

    public function reporte(SesionAsistencia $sesion)
    {
        $bloque = ServicioAsistencia::bloque($sesion);

        return view('admin.deportes.asistencia.reporte', [
            'sesion'  => $sesion->load('cerradaPor.persona'),
            'bloque'  => $bloque,
            'reporte' => ServicioAsistencia::reporteEvento($sesion, $bloque),
        ]);
    }

    public function reportePdf(SesionAsistencia $sesion)
    {
        $bloque = ServicioAsistencia::bloque($sesion);

        return Pdf::loadView('admin.deportes.asistencia.reporte_pdf', [
            'sesion'  => $sesion->load('cerradaPor.persona'),
            'bloque'  => $bloque,
            'reporte' => ServicioAsistencia::reporteEvento($sesion, $bloque),
        ])->setPaper('letter', 'portrait')->stream('asistencia_' . $sesion->id_sad . '.pdf');
    }

    public function historial(DeporteCalendario $calendario)
    {
        if (! $calendario->esRecurrente()) {
            return redirect()->route('admin.deportes.calendarios.show', $calendario->id_cld)
                ->with('error', 'El historial por persona solo existe para actividades que se repiten cada semana. En los eventos de una sola vez revisa el reporte de cada evento.');
        }

        return view('admin.deportes.asistencia.historial', [
            'actividad' => $calendario->load(['programa', 'horarios.actividad', 'horarios.espacio']),
            'historial' => ServicioAsistencia::historial($calendario),
        ]);
    }

    public function historialPdf(DeporteCalendario $calendario)
    {
        abort_unless($calendario->esRecurrente(), 404);

        return Pdf::loadView('admin.deportes.asistencia.historial_pdf', [
            'actividad' => $calendario->load(['programa', 'sede', 'horarios.actividad', 'horarios.espacio']),
            'historial' => ServicioAsistencia::historial($calendario),
        ])->setPaper('letter', 'portrait')->stream('historial_asistencia_' . $calendario->id_cld . '.pdf');
    }
}
