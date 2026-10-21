<?php

namespace App\Http\Controllers\salud;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\salud\GrupoHorario;
use App\Models\salud\Horario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GrupoHorarioController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $grupos = GrupoHorario::obtenerConHorarios($userId);
        $tieneCitasPendientes = Horario::hasPendingCitas($userId);

        return view('admin.psicologia.maestros.grupos_horarios.index', compact('grupos', 'tieneCitasPendientes'));
    }

    public function create()
    {
        if (Horario::hasPendingCitas(Auth::id())) {
            return redirect()->route('admin.psicologia.maestros.grupos_horarios.index')->with('error', 'No puedes modificar grupos de horarios mientras tengas citas pendientes o confirmadas.');
        }

        return view('admin.psicologia.maestros.grupos_horarios.create');
    }

    public function store(Request $request)
    {
        $userId = Auth::id();

        if (Horario::hasPendingCitas($userId)) {
            return redirect()->route('admin.psicologia.maestros.grupos_horarios.index')->with('error', 'No puedes modificar grupos de horarios mientras tengas citas pendientes o confirmadas.');
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        if (GrupoHorario::existeNombre($userId, $request->nombre)) {
            return redirect()->route('admin.psicologia.maestros.grupos_horarios.index')
                ->with('error', 'Ya existe un grupo con ese nombre. Usa un nombre diferente.');
        }

        if (!Horario::hasBloques($userId)) {
            return redirect()->route('admin.psicologia.maestros.grupos_horarios.index')
                ->with('error', 'No puedes crear un grupo sin bloques. Agrega al menos un bloque de horario primero.');
        }

        GrupoHorario::crear([
            'user_id' => $userId,
            'nombre' => $request->nombre,
            'activo' => GrupoHorario::STATUS_INACTIVE,
        ]);

        return redirect()->route('admin.psicologia.maestros.grupos_horarios.index')
            ->with('success', 'Grupo de horario creado correctamente.');
    }

    public function show($id)
    {
        $grupoHorario = GrupoHorario::obtenerPorId($id);
        if (!$grupoHorario || $grupoHorario->user_id !== Auth::id()) {
            abort(403, 'No autorizado.');
        }

        if ($grupoHorario->activo == GrupoHorario::STATUS_DELETED) {
            return redirect()->route('admin.psicologia.maestros.grupos_horarios.index')->with('error', 'Este grupo ha sido eliminado.');
        }

        $dias = Horario::diasSemana();
        $horarios = Horario::obtenerPorGrupo($grupoHorario->id);

        $horariosPorDia = [];
        foreach ($dias as $dia) {
            $horariosPorDia[$dia] = $horarios->where('dia', $dia);
        }

        $tieneCitasPendientes = Horario::hasPendingCitas(Auth::id());

        return view('admin.psicologia.maestros.grupos_horarios.show', compact('grupoHorario', 'horarios', 'horariosPorDia', 'dias', 'tieneCitasPendientes'));
    }

    public function edit($id)
    {
        $grupoHorario = GrupoHorario::obtenerPorId($id);
        if (!$grupoHorario || $grupoHorario->user_id !== Auth::id()) {
            abort(403, 'No autorizado.');
        }

        if ($grupoHorario->activo == GrupoHorario::STATUS_DELETED) {
            return redirect()->route('admin.psicologia.maestros.grupos_horarios.index')->with('error', 'Este grupo ha sido eliminado.');
        }

        if (Horario::hasPendingCitas(Auth::id())) {
            return redirect()->route('admin.psicologia.maestros.grupos_horarios.index')->with('error', 'No puedes modificar grupos de horarios mientras tengas citas pendientes o confirmadas.');
        }

        return view('admin.psicologia.maestros.grupos_horarios.edit', compact('grupoHorario'));
    }

    public function update(Request $request, $id)
    {
        $userId = Auth::id();
        $grupoHorario = GrupoHorario::obtenerPorId($id);
        if (!$grupoHorario || $grupoHorario->user_id !== $userId) {
            abort(403, 'No autorizado.');
        }

        if ($grupoHorario->activo == GrupoHorario::STATUS_DELETED) {
            return redirect()->route('admin.psicologia.maestros.grupos_horarios.index')->with('error', 'Este grupo ha sido eliminado.');
        }

        if (Horario::hasPendingCitas($userId)) {
            return redirect()->route('admin.psicologia.maestros.grupos_horarios.index')->with('error', 'No puedes modificar grupos de horarios mientras tengas citas pendientes o confirmadas.');
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        if (GrupoHorario::existeNombre($userId, $request->nombre, $grupoHorario->id)) {
            return redirect()->route('admin.psicologia.maestros.grupos_horarios.index')
                ->with('error', 'Ya existe otro grupo con ese nombre. Cambia el nombre.');
        }

        GrupoHorario::actualizar($grupoHorario->id, [
            'nombre' => $request->nombre,
        ]);

        return redirect()->route('admin.psicologia.maestros.grupos_horarios.index')
            ->with('success', 'Grupo de horario actualizado correctamente.');
    }

    public function destroy(Request $request, $id)
    {
        $grupoHorario = GrupoHorario::obtenerPorId($id);
        if (!$grupoHorario || $grupoHorario->user_id !== Auth::id()) {
            abort(403, 'No autorizado.');
        }

        if ($grupoHorario->activo == GrupoHorario::STATUS_DELETED) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Este grupo ya ha sido eliminado.'], 400);
            }
            return redirect()->route('admin.psicologia.maestros.grupos_horarios.index')->with('error', 'Este grupo ya ha sido eliminado.');
        }

        if (Horario::hasPendingCitas(Auth::id())) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'No puedes modificar grupos de horarios mientras tengas citas pendientes o confirmadas.'], 400);
            }
            return redirect()->route('admin.psicologia.maestros.grupos_horarios.index')->with('error', 'No puedes modificar grupos de horarios mientras tengas citas pendientes o confirmadas.');
        }

        GrupoHorario::eliminar($id);

        if ($request->wantsJson() || $request->ajax()) {
            session()->flash('success', 'Grupo de horario eliminado correctamente.');
            return response()->json([
                'status' => 'success',
                'message' => 'Grupo de horario eliminado correctamente.'
            ], 200);
        }

        return redirect()->route('admin.psicologia.maestros.grupos_horarios.index')
            ->with('success', 'Grupo de horario eliminado correctamente.');
    }

    public function storeFromHorarios(Request $request)
    {
        $rules = [
            'action' => 'required|in:update,create',
        ];

        if ($request->input('action') === 'create') {
            $rules['nombre'] = 'required|string|max:255';
        } else {
            $rules['nombre'] = 'nullable|string|max:255';
        }

        $request->validate($rules);

        $userId = Auth::id();
        $action = $request->input('action');
        $nombre = $request->input('nombre');

        if (Horario::hasPendingCitas($userId)) {
            return redirect()->route('admin.psicologia.maestros.horarios.index')->with('error', 'No puedes cambiar el grupo de horarios mientras tengas citas pendientes.');
        }

        try {
            $message = GrupoHorario::crearDesdeHorarios($userId, $action, $nombre);
            return redirect()->route('admin.psicologia.maestros.horarios.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.psicologia.maestros.horarios.index')->with('error', \App\Support\Seguridad\MensajeError::de($e));
        }
    }

    public function activate(Request $request, $id)
    {
        $grupoHorario = GrupoHorario::obtenerPorId($id);
        if (!$grupoHorario || $grupoHorario->user_id !== Auth::id()) {
            abort(403, 'No autorizado.');
        }

        if ($grupoHorario->activo == GrupoHorario::STATUS_DELETED) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Este grupo ha sido eliminado.'
                ], 400);
            }

            return redirect()->route('admin.psicologia.maestros.grupos_horarios.index')->with('error', 'Este grupo ha sido eliminado.');
        }

        if (Horario::hasPendingCitas(Auth::id())) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No puedes cambiar o activar otro grupo de horarios mientras tengas citas pendientes o confirmadas.'
                ], 400);
            }

            return redirect()->route('admin.psicologia.maestros.grupos_horarios.index')->with('error', 'No puedes cambiar o activar otro grupo de horarios mientras tengas citas pendientes o confirmadas.');
        }

        $count = DB::table('horarios')
            ->where('grupo_horario_id', $id)
            ->where('activo', '!=', Horario::STATUS_DELETED)
            ->count();
        if ($count < 2) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No puedes activar un grupo con menos de dos bloques de horario.'
                ], 400);
            }
            return redirect()->route('admin.psicologia.maestros.grupos_horarios.index')->with('error', 'No puedes activar un grupo con menos de dos bloques de horario.');
        }

        GrupoHorario::activate($id);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Grupo de horario activado correctamente.'
            ], 200);
        }

        return redirect()->route('admin.psicologia.maestros.grupos_horarios.index')
            ->with('success', 'Grupo de horario activado correctamente.');
    }

    public function deactivate(Request $request, $id)
    {
        $grupoHorario = GrupoHorario::obtenerPorId($id);
        if (!$grupoHorario || $grupoHorario->user_id !== Auth::id()) {
            abort(403, 'No autorizado.');
        }

        if ($grupoHorario->activo == GrupoHorario::STATUS_DELETED) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Este grupo ha sido eliminado.'
                ], 400);
            }

            return redirect()->route('admin.psicologia.maestros.grupos_horarios.index')->with('error', 'Este grupo ha sido eliminado.');
        }

        if (Horario::hasPendingCitas(Auth::id())) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No puedes cambiar o desactivar el grupo de horarios mientras tengas citas pendientes o confirmadas.'
                ], 400);
            }

            return redirect()->route('admin.psicologia.maestros.grupos_horarios.index')->with('error', 'No puedes cambiar o desactivar el grupo de horarios mientras tengas citas pendientes o confirmadas.');
        }

        GrupoHorario::deactivate($id);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Grupo de horario desactivado correctamente.'
            ], 200);
        }

        return redirect()->route('admin.psicologia.maestros.grupos_horarios.index')
            ->with('success', 'Grupo de horario desactivado correctamente.');
    }
}
