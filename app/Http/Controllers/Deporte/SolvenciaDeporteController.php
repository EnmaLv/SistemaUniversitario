<?php

namespace App\Http\Controllers\Deporte;

use App\Http\Controllers\Controller;
use App\Services\Deporte\PrestamoService;
use App\Services\Solvencia\SolvenciaService;
use App\Support\Sedes\SedeActiva;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SolvenciaDeporteController extends Controller
{
    public function __construct(private SolvenciaService $solvencia)
    {
    }

    public function index(Request $request)
    {
        $cedula = trim((string) $request->query('cedula', ''));
        $resultado = null;
        $noEncontrada = false;

        if ($cedula !== '') {
            $persona = PrestamoService::buscarPersona($cedula);

            if ($persona) {
                $resultado = $this->solvencia->consultar($persona, ['deporte']);
                $this->solvencia->registrarConsulta($resultado, 'pantalla');
            } else {
                $noEncontrada = true;
            }
        }

        return view('admin.deportes.solvencia.index', compact('cedula', 'resultado', 'noEncontrada'));
    }

    public function pdf(Request $request)
    {
        $request->validate(['cedula' => ['required', 'string', 'max:20']]);

        $persona = PrestamoService::buscarPersona($request->query('cedula'));

        if (! $persona) {
            return redirect()->route('admin.deportes.solvencia.index', ['cedula' => $request->query('cedula')])
                ->with('error', 'No existe ninguna persona registrada con esa cédula.');
        }

        $resultado = $this->solvencia->consultar($persona, ['deporte']);
        $this->solvencia->registrarConsulta($resultado, 'pdf');

        $usuario = Auth::user();
        $emisor = trim((string) ($usuario?->nombre_completo ?: $usuario?->username_use));
        $sede = DB::table('sede')->where('id_sed', SedeActiva::idParaCrear())->value('nombre_sed');

        $pdf = Pdf::loadView('admin.deportes.solvencia.pdf', compact('resultado', 'emisor', 'sede'))->setPaper('a4', 'portrait');

        $nombre = ($resultado['solvente'] ? 'constancia_solvencia_' : 'constancia_no_solvencia_') . $persona->id_per . '_' . now()->format('Y-m-d') . '.pdf';

        return $pdf->stream($nombre);
    }
}
