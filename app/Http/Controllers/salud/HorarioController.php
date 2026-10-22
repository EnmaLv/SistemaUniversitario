<?php

namespace App\Http\Controllers\salud;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\salud\Horario;
use App\Models\salud\GrupoHorario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HorarioController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $dias = Horario::diasSemana();
        $filtroDia = $request->get('dia');
        $grupoId = $request->get('grupo');

        $grupoActivo = GrupoHorario::obtenerActivoPorPsicologo($userId);

        $grupoSeleccionado = null;
        if ($grupoId) {
            $grupoSeleccionado = GrupoHorario::obtenerPorIdYUsuario($grupoId, $userId);

            if ($grupoActivo && $grupoSeleccionado && (int) $grupoActivo->id_gha === (int) $grupoSeleccionado->id_gha) {
                $request->session()->reflash();
                return redirect()->route('admin.psicologia.maestros.horarios.index', $filtroDia ? ['dia' => $filtroDia] : []);
            }
        }

        $currentGrupoId = $grupoSeleccionado ? $grupoSeleccionado->id_gha : ($grupoActivo ? $grupoActivo->id_gha : null);
        $horarios = Horario::obtenerPorFiltros($userId, $currentGrupoId, $filtroDia);

        $horariosPorDia = [];
        foreach ($dias as $dia) {
            $horariosPorDia[$dia] = $horarios->where('dia_hpr', $dia);
        }

        $tieneCitasPendientes = Horario::hasPendingCitas($userId);

        return view('admin.psicologia.maestros.horarios.index', compact('horarios', 'horariosPorDia', 'dias', 'filtroDia', 'grupoActivo', 'tieneCitasPendientes', 'grupoSeleccionado'));
    }

    public function create(Request $request)
    {
        $dias = Horario::diasSemana();
        $grupoRetorno = $request->query('grupo');
        $userId = Auth::id();

        $tieneCitasPendientes = Horario::hasPendingCitas($userId);

        $grupoActivo = GrupoHorario::obtenerActivoPorPsicologo($userId);

        $grupoSeleccionado = null;
        if ($grupoRetorno) {
            $grupoSeleccionado = GrupoHorario::obtenerPorIdYUsuario($grupoRetorno, $userId);
        }

        $currentGrupoId = $grupoSeleccionado
            ? $grupoSeleccionado->id_gha
            : ($grupoActivo ? $grupoActivo->id_gha : null);

        // Bloques existentes para el contexto actual
        $horariosExistentes = Horario::obtenerPorFiltros($userId, $currentGrupoId, null);

        $horariosSeleccionadosIniciales = [];
        foreach ($horariosExistentes as $h) {
            $inicio = \Carbon\Carbon::parse($h->hora_inicio_hpr)->format('H:i');
            $fin    = \Carbon\Carbon::parse($h->hora_fin_hpr)->format('H:i');
            $horariosSeleccionadosIniciales[] = "{$h->dia_hpr}|{$inicio}|{$fin}";
        }

        return view('admin.psicologia.maestros.horarios.create', compact(
            'dias',
            'grupoRetorno',
            'tieneCitasPendientes',
            'grupoSeleccionado',
            'grupoActivo',
            'horariosSeleccionadosIniciales'
        ));
    }

    public function edit(Request $request, $id)
    {
        $horario = Horario::obtenerPorId($id);
        abort_if(!$horario || $horario->deleted_at !== null || (int) $horario->psicologo_use_id !== (int) Auth::id(), 403);

        if (Horario::hasPendingCitas(Auth::id())) {
            return redirect()->route('admin.psicologia.maestros.horarios.index')->with('error', 'No puedes modificar bloques de horario mientras tengas citas pendientes o confirmadas.');
        }

        $dias = Horario::diasSemana();
        $grupoRetorno = $request->query('grupo', $horario->gha_id);

        return view('admin.psicologia.maestros.horarios.edit', compact('horario', 'dias', 'grupoRetorno'));
    }

    public function store(Request $request)
    {
        if (Horario::hasPendingCitas(Auth::id())) {
            return redirect()
                ->route('admin.psicologia.maestros.horarios.index')
                ->with('error', 'No puedes modificar bloques de horario mientras tengas citas pendientes o confirmadas.');
        }

        $validated = $request->validate([
            'grupo_id'   => 'nullable|exists:grupo_horario_atencion,id_gha',
            'horarios'   => 'nullable|array',
            'horarios.*' => 'string',
        ]);

        $userId = Auth::id();
        $grupoEspecificoId = $validated['grupo_id'] ?? null;

        if ($grupoEspecificoId) {
            $grupoAModificar = GrupoHorario::obtenerPorIdYUsuario($grupoEspecificoId, $userId);
            $activoPorDefecto = ($grupoAModificar && (int) $grupoAModificar->status_id === GrupoHorario::STATUS_ACTIVE)
                ? Horario::STATUS_ACTIVE
                : Horario::STATUS_INACTIVE;
            $grupoAsignarId = $grupoAModificar ? $grupoAModificar->id_gha : null;
        } else {
            $grupoActivo = GrupoHorario::obtenerActivoPorPsicologo($userId);
            $activoPorDefecto = $grupoActivo ? Horario::STATUS_ACTIVE : Horario::STATUS_INACTIVE;
            $grupoAsignarId = $grupoActivo ? $grupoActivo->id_gha : null;
        }

        // Normalizar seleccionados
        $seleccionados = $request->input('horarios', []);
        $clavesSeleccionadas = [];
        foreach ($seleccionados as $item) {
            $partes = explode('|', $item);
            if (count($partes) < 3) continue;

            [$dia, $horaInicio, $horaFin] = $partes;

            if (!in_array($dia, Horario::diasSemana())) continue;

            $clavesSeleccionadas["{$dia}|{$horaInicio}|{$horaFin}"] = [
                'dia'    => $dia,
                'inicio' => $horaInicio,
                'fin'    => $horaFin,
            ];
        }

        // Mapa de bloques existentes
        $existentes = Horario::obtenerPorFiltros($userId, $grupoAsignarId, null);
        $clavesExistentes = [];
        foreach ($existentes as $h) {
            $inicio = \Carbon\Carbon::parse($h->hora_inicio_hpr)->format('H:i');
            $fin    = \Carbon\Carbon::parse($h->hora_fin_hpr)->format('H:i');
            $clavesExistentes["{$h->dia_hpr}|{$inicio}|{$fin}"] = $h;
        }

        $creados = 0;
        $eliminados = 0;

        // Eliminar los que ya no están seleccionados
        foreach ($clavesExistentes as $clave => $h) {
            if (!isset($clavesSeleccionadas[$clave])) {
                Horario::eliminar($h->id_hpr);
                $eliminados++;
            }
        }

        // Crear los nuevos
        foreach ($clavesSeleccionadas as $clave => $data) {
            if (!isset($clavesExistentes[$clave])) {
                Horario::crear([
                    'psicologo_use_id' => $userId,
                    'dia_hpr'          => $data['dia'],
                    'hora_inicio_hpr'  => $data['inicio'],
                    'hora_fin_hpr'     => $data['fin'],
                    'descripcion_hpr'  => null,
                    'status_id'        => $activoPorDefecto,
                    'gha_id'           => $grupoAsignarId,
                ]);
                $creados++;
            }
        }

        $msg = 'Horario actualizado correctamente.';
        if ($creados > 0 || $eliminados > 0) {
            $msg = "Se agregaron {$creados} bloque(s) y se eliminaron {$eliminados} bloque(s).";
        }

        return redirect()
            ->route('admin.psicologia.maestros.horarios.index', $grupoAsignarId ? ['grupo' => $grupoAsignarId] : [])
            ->with('success', $msg);
    }

    public function show(Request $request, $id)
    {
        $horario = Horario::obtenerPorId($id);
        abort_if(!$horario || $horario->deleted_at !== null || (int) $horario->psicologo_use_id !== (int) Auth::id(), 403);
        $grupoRetorno = $request->query('grupo', $horario->gha_id);

        return view('admin.psicologia.maestros.horarios.show', compact('horario', 'grupoRetorno'));
    }

    public function update(Request $request, $id)
    {
        $horario = Horario::obtenerPorId($id);
        abort_if(!$horario || $horario->deleted_at !== null || (int) $horario->psicologo_use_id !== (int) Auth::id(), 403);

        if (Horario::hasPendingCitas(Auth::id())) {
            return redirect()->route('admin.psicologia.maestros.horarios.index')->with('error', 'No puedes modificar bloques de horario mientras tengas citas pendientes o confirmadas.');
        }

        $horaInicio = $this->parseTimeInput(
            $request->input('hora_inicio_hora'),
            $request->input('hora_inicio_minuto'),
            $request->input('hora_inicio_periodo')
        );
        $horaFin = $this->parseTimeInput(
            $request->input('hora_fin_hora'),
            $request->input('hora_fin_minuto'),
            $request->input('hora_fin_periodo')
        );

        $request->merge([
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
        ]);

        $validated = $request->validate([
            'dia' => 'required|string|in:' . implode(',', Horario::diasSemana()),
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'descripcion' => 'nullable|string',
        ]);

        if (Horario::overlaps(Auth::id(), $validated['dia'], $validated['hora_inicio'], $validated['hora_fin'], $horario->id_hpr, $horario->gha_id)) {
            return back()
                ->withErrors(['hora_inicio' => 'El bloque de horario se superpone con otro existente.'])
                ->withInput();
        }

        Horario::actualizar($horario->id_hpr, [
            'dia_hpr' => $validated['dia'],
            'hora_inicio_hpr' => $validated['hora_inicio'],
            'hora_fin_hpr' => $validated['hora_fin'],
            'descripcion_hpr' => $validated['descripcion'],
        ]);

        return redirect()
            ->route('admin.psicologia.maestros.horarios.index', $horario->gha_id ? ['grupo' => $horario->gha_id] : [])
            ->with('success', 'Bloque de tiempo actualizado correctamente.');
    }

    public function destroy(Request $request, $id)
    {
        $horario = Horario::obtenerPorId($id);
        abort_if(!$horario || $horario->deleted_at !== null || (int) $horario->psicologo_use_id !== (int) Auth::id(), 403);

        if (Horario::hasPendingCitas(Auth::id())) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No puedes modificar bloques de horario mientras tengas citas pendientes o confirmadas.'
                ], 400);
            }
            return redirect()->route('admin.psicologia.maestros.horarios.index')->with('error', 'No puedes modificar bloques de horario mientras tengas citas pendientes o confirmadas.');
        }

        $grupoId = $horario->gha_id;
        if ($grupoId) {
            $count = DB::table('horario_profesional')
                ->where('gha_id', $grupoId)
                ->whereNull('deleted_at')
                ->count();
            if ($count <= 2) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'No puedes eliminar este bloque porque el grupo de horarios debe tener al menos dos bloques.'
                    ], 400);
                }
                return redirect()->route('admin.psicologia.maestros.horarios.index', ['grupo' => $grupoId])
                    ->with('error', 'No puedes eliminar este bloque porque el grupo de horarios debe tener al menos dos bloques.');
            }
        }

        Horario::eliminar($horario->id_hpr);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Bloque de horario eliminado correctamente.',
                'grupo_id' => $grupoId
            ], 200);
        }

        return redirect()
            ->route('admin.psicologia.maestros.horarios.index', $grupoId ? ['grupo' => $grupoId] : [])
            ->with('success', 'Bloque de horario eliminado correctamente.');
    }

    public function activate($id)
    {
        $horario = Horario::obtenerPorId($id);
        abort_if(!$horario || $horario->deleted_at !== null || (int) $horario->psicologo_use_id !== (int) Auth::id(), 403);

        if (Horario::hasPendingCitas(Auth::id())) {
            return redirect()->route('admin.psicologia.maestros.horarios.index')->with('error', 'No puedes modificar bloques de horario mientras tengas citas pendientes o confirmadas.');
        }

        Horario::actualizar($horario->id_hpr, ['status_id' => Horario::STATUS_ACTIVE]);

        return redirect()->route('admin.psicologia.maestros.horarios.index')->with('success', 'Bloque de tiempo activado.');
    }

    public function deactivate($id)
    {
        $horario = Horario::obtenerPorId($id);
        abort_if(!$horario || $horario->deleted_at !== null || (int) $horario->psicologo_use_id !== (int) Auth::id(), 403);

        if (Horario::hasPendingCitas(Auth::id())) {
            return redirect()->route('admin.psicologia.maestros.horarios.index')->with('error', 'No puedes modificar bloques de horario mientras tengas citas pendientes o confirmadas.');
        }

        Horario::actualizar($horario->id_hpr, ['status_id' => Horario::STATUS_INACTIVE]);

        return redirect()->route('admin.psicologia.maestros.horarios.index')->with('success', 'Bloque de tiempo desactivado.');
    }

    private function parseTimeInput($hora, $minuto, $periodo)
    {
        if (empty($hora) || empty($minuto) || empty($periodo)) {
            return null;
        }

        $hora = (int)$hora;
        if ($periodo === 'PM' && $hora < 12) {
            $hora += 12;
        } elseif ($periodo === 'AM' && $hora === 12) {
            $hora = 0;
        }

        return str_pad($hora, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minuto, 2, '0', STR_PAD_LEFT);
    }

    public function exportarPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        $userId = Auth::id();
        $dias = Horario::diasSemana();
        $grupoId = $request->get('grupo');

        $grupoActivo = GrupoHorario::obtenerActivoPorPsicologo($userId);
        $grupoSeleccionado = null;
        if ($grupoId) {
            $grupoSeleccionado = GrupoHorario::obtenerPorIdYUsuario($grupoId, $userId);
        }
        $currentGrupoId = $grupoSeleccionado ? $grupoSeleccionado->id_gha : ($grupoActivo ? $grupoActivo->id_gha : null);

        $horarios = Horario::obtenerPorFiltros($userId, $currentGrupoId, null);

        $horariosPorDia = [];
        foreach ($dias as $dia) {
            $horariosPorDia[$dia] = $horarios->where('dia_hpr', $dia);
        }

        $psicologo = \App\Models\Usuario::obtenerUsuarioPorId($userId);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.psicologia.maestros.horarios.pdf', compact('horariosPorDia', 'dias', 'psicologo'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('Horario_Psicologo_' . \Illuminate\Support\Str::slug($psicologo->name) . '.pdf');
    }
}
