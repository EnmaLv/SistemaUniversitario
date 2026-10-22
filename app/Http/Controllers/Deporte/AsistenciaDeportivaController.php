<?php

namespace App\Http\Controllers\Deporte;

use App\Http\Controllers\Controller;
use App\Models\Deporte\AsistenciaDeportiva;
use App\Models\Deporte\DeporteHorario;
use App\Models\Deporte\InscripcionDeportiva;
use App\Models\Deporte\SesionAsistencia;
use App\Support\Deportes\ServicioAsistencia;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AsistenciaDeportivaController extends Controller
{
    private function horariosSeleccionables()
    {
        return DeporteHorario::query()
            ->with(['actividad', 'espacio'])
            ->whereIn('estado_hde', [DeporteHorario::ESTADO_ACTIVO, DeporteHorario::ESTADO_COMPLETADO])
            ->orderBy('hora_inicio_hde')
            ->get()
            ->sortBy(fn ($h) => ($h->actividad?->nombre_acd ?? '') . ' ' . array_search($h->diaClave(), DeporteHorario::DIAS))
            ->values();
    }

    private function fechaValida($valor): Carbon
    {
        try {
            return $valor ? Carbon::parse($valor)->startOfDay() : Carbon::today();
        } catch (\Throwable $e) {
            return Carbon::today();
        }
    }

    public function index(Request $request)
    {
        $horarios = $this->horariosSeleccionables();
        $fecha = $this->fechaValida($request->query('fecha'));
        $horario = null;
        $sesion = null;
        $inscritos = collect();
        $marcas = collect();
        $fechasSugeridas = [];
        $sesionesRecientes = collect();

        if ($request->filled('hde_id')) {
            $horario = DeporteHorario::query()->with(['actividad', 'espacio', 'calendario'])->find($request->integer('hde_id'));
        }

        if ($horario) {
            $sesion = ServicioAsistencia::sesionDe($horario, $fecha);

            if ($sesion) {
                $inscritos = ServicioAsistencia::inscritosDeSesion($sesion);
                $marcas = AsistenciaDeportiva::query()->where('sad_id', $sesion->id_sad)->pluck('estado_asd', 'ide_id');
            } else {
                $inscritos = $horario->inscripciones()
                    ->with('persona')
                    ->where('estado_ide', '!=', InscripcionDeportiva::ESTADO_RETIRADO)
                    ->whereDate('fecha_registro_ide', '<=', $fecha->toDateString())
                    ->get();
            }

            for ($dia = Carbon::today(), $i = 0; $i < 120 && count($fechasSugeridas) < 6; $i++, $dia->subDay()) {
                if ($horario->aplicaEnFecha($dia)) {
                    $fechasSugeridas[] = $dia->copy();
                }
            }

            $sesionesRecientes = SesionAsistencia::query()
                ->where('hde_id', $horario->id_hde)
                ->withCount([
                    'asistencias as presentes_count' => fn ($q) => $q->where('estado_asd', AsistenciaDeportiva::ESTADO_PRESENTE),
                    'asistencias as inasistentes_count' => fn ($q) => $q->where('estado_asd', AsistenciaDeportiva::ESTADO_INASISTENTE),
                ])
                ->orderByDesc('fecha_sad')
                ->limit(8)
                ->get();
        }

        return view('admin.deportes.asistencia.index', compact('horarios', 'horario', 'fecha', 'sesion', 'inscritos', 'marcas', 'fechasSugeridas', 'sesionesRecientes'));
    }

    public function store(Request $request)
    {
        $validado = $request->validate([
            'hde_id' => ['required', 'integer'],
            'fecha'  => ['required', 'date'],
        ], [
            'hde_id.required' => 'Selecciona un horario.',
            'fecha.required'  => 'Indica la fecha de la sesión.',
        ]);

        $horario = DeporteHorario::query()->with('calendario')->findOrFail($validado['hde_id']);
        $sesion = ServicioAsistencia::abrir($horario, $validado['fecha']);

        return redirect()->route('admin.deportes.asistencia.index', ['hde_id' => $horario->id_hde, 'fecha' => $sesion->fecha_sad->format('Y-m-d')])
            ->with('success', 'Sesión de asistencia abierta.');
    }

    public function update(Request $request, SesionAsistencia $sesion)
    {
        $presentes = array_filter((array) $request->input('presentes', []), 'is_numeric');

        ServicioAsistencia::marcar($sesion, $presentes);

        return redirect()->route('admin.deportes.asistencia.index', ['hde_id' => $sesion->hde_id, 'fecha' => $sesion->fecha_sad->format('Y-m-d')])
            ->with('success', 'Asistencia guardada. La sesión sigue abierta.');
    }

    public function cerrar(Request $request, SesionAsistencia $sesion)
    {
        $presentes = $request->has('marcas_enviadas')
            ? array_filter((array) $request->input('presentes', []), 'is_numeric')
            : null;

        $sesion = ServicioAsistencia::cerrar($sesion, $presentes);

        return redirect()->route('admin.deportes.asistencia.index', ['hde_id' => $sesion->hde_id, 'fecha' => $sesion->fecha_sad->format('Y-m-d')])
            ->with('success', 'Sesión cerrada. Los inscritos no marcados quedaron como inasistentes.');
    }

    private function datosReporte(Request $request): array
    {
        $horario = $request->filled('hde_id')
            ? DeporteHorario::query()->with(['actividad', 'espacio', 'calendario.sede'])->find($request->integer('hde_id'))
            : null;

        $hasta = $this->fechaValida($request->query('hasta'));
        $desde = $request->filled('desde')
            ? $this->fechaValida($request->query('desde'))
            : ($horario?->calendario?->fecha_inicio_cld?->copy() ?? $hasta->copy()->subMonth());

        if ($desde->gt($hasta)) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        $reporte = $horario ? ServicioAsistencia::reporte($horario, $desde, $hasta) : null;

        return compact('horario', 'desde', 'hasta', 'reporte');
    }

    public function reporte(Request $request)
    {
        return view('admin.deportes.asistencia.reporte', $this->datosReporte($request) + [
            'horarios' => $this->horariosSeleccionables(),
        ]);
    }

    public function reportePdf(Request $request)
    {
        $datos = $this->datosReporte($request);

        abort_unless($datos['horario'], 404);

        return Pdf::loadView('admin.deportes.asistencia.reporte_pdf', $datos)
            ->setPaper('letter', 'portrait')
            ->stream('asistencia_deportiva_' . $datos['horario']->id_hde . '.pdf');
    }
}
