<?php

namespace App\Http\Controllers\Deporte;

use App\Http\Controllers\Controller;
use App\Models\Deporte\DeporteCalendario;
use App\Models\Deporte\DeporteEspacio;
use App\Support\Deportes\ServicioEventos;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HorarioDeporteController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.deportes.maestros.horarios.index', $this->agenda($request));
    }

    public function exportarPdf(Request $request)
    {
        $datos = $this->agenda($request);

        return Pdf::loadView('admin.deportes.maestros.horarios.pdf', $datos)
            ->setPaper('a4', 'landscape')
            ->stream('agenda_deportes_' . $datos['lunes']->format('Y-m-d') . '.pdf');
    }

    private function agenda(Request $request): array
    {
        try {
            $lunes = Carbon::parse($request->query('semana') ?: now())->startOfWeek(Carbon::MONDAY)->startOfDay();
        } catch (\Throwable $e) {
            $lunes = Carbon::now()->startOfWeek(Carbon::MONDAY)->startOfDay();
        }

        $domingo = $lunes->copy()->addDays(6);
        $espacioId = $request->integer('epd_id') ?: null;

        $eventos = ServicioEventos::listar($lunes, $domingo, null, false)
            ->when($espacioId, fn ($lista) => $lista->filter(fn ($evento) => (int) $evento->bloque->epd_id === $espacioId))
            ->values();

        $espacios = DeporteEspacio::query()->where('status_id', 1)->orderBy('nombre_epd')->get(['id_epd', 'nombre_epd', 'ubicacion_epd']);

        $espaciosConEventos = $eventos->pluck('bloque.espacio')->filter()->unique('id_epd');

        $filas = $espacios->concat($espaciosConEventos->reject(fn ($espacio) => $espacios->contains('id_epd', $espacio->id_epd)))
            ->when($espacioId, fn ($lista) => $lista->filter(fn ($espacio) => (int) $espacio->id_epd === $espacioId))
            ->sortBy('nombre_epd')
            ->values();

        $dias = [];

        for ($i = 0; $i < 7; $i++) {
            $fecha = $lunes->copy()->addDays($i);
            $dias[] = [
                'fecha'  => $fecha,
                'clave'  => $fecha->toDateString(),
                'nombre' => DeporteCalendario::DIAS_LEGIBLES[DeporteCalendario::diaDeFecha($fecha)],
                'hoy'    => $fecha->isToday(),
            ];
        }

        $celdas = $eventos->groupBy(fn ($evento) => $evento->bloque->epd_id . '|' . $evento->fecha->toDateString());

        return [
            'lunes'     => $lunes,
            'domingo'   => $domingo,
            'dias'      => $dias,
            'filas'     => $filas,
            'celdas'    => $celdas,
            'eventos'   => $eventos,
            'espacios'  => $espacios,
            'espacioId' => $espacioId,
        ];
    }
}
