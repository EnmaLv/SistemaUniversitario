<?php

namespace App\Http\Controllers\salud;

use App\Http\Controllers\Controller;
use App\Models\salud\Consultorio;
use App\Models\salud\HorarioConsultorio;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class HorarioConsultorioController extends Controller
{
    public function index(Request $request)
    {
        $consultorios = Consultorio::where('status_id', Consultorio::STATUS_ACTIVO)->orderBy('nombre_csr')->get();

        $consultorioId = (int) $request->input('consultorio_id', optional($consultorios->first())->id_csr);
        $estadoFilter  = $request->input('estado', 'todos');

        $horarios = $consultorioId
            ? HorarioConsultorio::porConsultorioAgrupado($consultorioId)
            : collect();

        return view('admin.salud.movimientos.horarios.index', compact(
            'consultorios',
            'consultorioId',
            'horarios',
            'estadoFilter'
        ));
    }

    /**
     * Formulario de selección de bloques. Usuarios elegibles y horarios ya
     * asignados se resuelven a través de la relación HorarioConsultorio -> RolUsuario -> Usuario -> Persona.
     */
    public function create(Request $request)
    {
        $consultorios = Consultorio::where('status_id', Consultorio::STATUS_ACTIVO)->orderBy('nombre_csr')->get();
        $consultorioSeleccionado = $request->input('consultorio_id');
        $ocupadosPorConsultorio = HorarioConsultorio::ocupadosPorConsultorio();

        $usuariosElegibles = HorarioConsultorio::usuariosElegibles();

        $horariosActuales = collect();

        if ($consultorioSeleccionado) {
            $horariosActuales = HorarioConsultorio::with('rolUsuario.usuario.persona', 'rolUsuario.rol')
                ->where('csr_id', $consultorioSeleccionado)
                ->where('status_id', HorarioConsultorio::STATUS_ACTIVO)
                ->get()
                ->map(function ($h) {
                    $dia = strtolower(trim($h->dia_hrc));
                    $dia = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $dia);

                    return [
                        'clave_simple'   => "{$dia}|{$h->hora_inicio_hrc}|{$h->hora_fin_hrc}",
                        'usr_id' => $h->usr_id,
                        'nombre_usuario' => $h->nombre_personal,
                        'rol_usuario'    => $h->nombre_rol_asignado,
                    ];
                });
        }

        return view('admin.salud.movimientos.horarios.create', compact(
            'consultorios',
            'consultorioSeleccionado',
            'ocupadosPorConsultorio',
            'horariosActuales',
            'usuariosElegibles'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'consultorio_id' => ['required', 'exists:consultorio,id_csr'],
            'horarios'       => ['nullable', 'array'],
            'horarios.*'     => ['string'],
        ], [
            'consultorio_id.required' => 'Selecciona un consultorio.',
        ]);

        $consultorioId = $validated['consultorio_id'];
        $seleccionados = $request->input('horarios', []);

        $existentes = HorarioConsultorio::where('csr_id', $consultorioId)->get();

        $existentesMap = $existentes->keyBy(function ($h) {
            $dia = strtolower(trim($h->dia_hrc));
            $dia = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $dia);
            return "{$dia}|{$h->hora_inicio_hrc}|{$h->hora_fin_hrc}|{$h->usr_id}";
        });

        $clavesProcesadas = [];

        foreach ($seleccionados as $item) {
            $partes = explode('|', $item);

            if (count($partes) < 4) {
                continue;
            }

            $dia          = strtolower(trim($partes[0]));
            $inicio       = $partes[1];
            $fin          = $partes[2];
            $idRolUsuario = !empty($partes[3]) ? (int) $partes[3] : null;

            if (!$idRolUsuario || !array_key_exists($dia, HorarioConsultorio::DIAS)) {
                continue;
            }

            $key = "{$dia}|{$inicio}|{$fin}|{$idRolUsuario}";
            $clavesProcesadas[] = $key;

            if ($existentesMap->has($key)) {
                $horario = $existentesMap->get($key);
                if (!$horario->estaActivo()) {
                    $horario->update(['status_id' => HorarioConsultorio::STATUS_ACTIVO]);
                }
            } else {
                HorarioConsultorio::create([
                    'csr_id'          => $consultorioId,
                    'usr_id'          => $idRolUsuario,
                    'dia_hrc'         => $dia,
                    'hora_inicio_hrc' => $inicio,
                    'hora_fin_hrc'    => $fin,
                    'status_id'       => HorarioConsultorio::STATUS_ACTIVO,
                ]);
            }
        }

        foreach ($existentes as $horario) {
            $dia = strtolower(trim($horario->dia_hrc));
            $dia = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $dia);
            $key = "{$dia}|{$horario->hora_inicio_hrc}|{$horario->hora_fin_hrc}|{$horario->usr_id}";

            if (!in_array($key, $clavesProcesadas) && $horario->estaActivo()) {
                $horario->update(['status_id' => HorarioConsultorio::STATUS_INACTIVO]);
            }
        }

        return redirect()
            ->route('admin.salud.movimientos.horarios.index', ['consultorio_id' => $consultorioId])
            ->with('success', 'Horarios asignados y actualizados correctamente.');
    }

    public function destroy(HorarioConsultorio $horario)
    {
        $consultorioId = $horario->csr_id;
        $horario->update(['status_id' => HorarioConsultorio::STATUS_INACTIVO]);

        return redirect()
            ->route('admin.salud.movimientos.horarios.index', ['consultorio_id' => $consultorioId])
            ->with('success', 'Bloque de horario eliminado.');
    }

    public function exportarPdf(Request $request)
    {
        ini_set('memory_limit', '512M');

        $consultorios = Consultorio::where('status_id', Consultorio::STATUS_ACTIVO)->orderBy('nombre_csr')->get();
        $consultorioId = (int) $request->input('consultorio_id', optional($consultorios->first())->id_csr);

        $consultorio = Consultorio::findOrFail($consultorioId);

        $horariosActivos = HorarioConsultorio::with('rolUsuario.usuario.persona', 'rolUsuario.rol')
            ->where('csr_id', $consultorioId)
            ->where('status_id', HorarioConsultorio::STATUS_ACTIVO)
            ->get();

        $activosMap = [];
        foreach ($horariosActivos as $h) {
            $dia = strtolower(trim($h->dia_hrc));
            $inicioKey = Carbon::parse($h->hora_inicio_hrc)->format('H:i');
            $finKey = Carbon::parse($h->hora_fin_hrc)->format('H:i');
            $activosMap["{$dia}|{$inicioKey}|{$finKey}"][] = $h;
        }

        // Agrupar asignaciones por persona para el detalle de la página 2
        $personalAsignado = $horariosActivos->groupBy('usr_id');

        $bloquesJornadas = HorarioConsultorio::BLOQUES;
        $dias = HorarioConsultorio::DIAS;

        $pdf = Pdf::loadView('admin.salud.movimientos.horarios.pdf', compact(
            'consultorio',
            'activosMap',
            'personalAsignado',
            'bloquesJornadas',
            'dias'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream('Horario_Consultorio_' . Str::slug($consultorio->nombre_csr) . '.pdf');
    }
}
