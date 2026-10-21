<?php

namespace App\Http\Controllers\salud;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\salud\Prioridad;

class PrioridadController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $buscar = $request->input('buscar');
        
        $prioridades = Prioridad::buscarYPaginar($buscar, $userId);
            
        return view('admin.psicologia.maestros.prioridades.index', compact('prioridades', 'buscar'));
    }

    public function create()
    {
        return view('admin.psicologia.maestros.prioridades.create');
    }

    public function store(Request $request)
    {
        $userId = Auth::id();
        
        $validated = $request->validate([
            'nombre' => 'required|string|max:50',
            'nivel_gravedad' => 'required|integer|in:2,3,4,6,8,9',
        ], [
            'nivel_gravedad.in' => 'El nivel seleccionado no es válido o está reservado por el sistema.',
        ]);

        $nombreNormalizado = strtolower(trim($validated['nombre']));
        $nivel = $validated['nivel_gravedad'];

        $existsName = DB::table('prioridad_atencion')
            ->where('nombre_pat', $nombreNormalizado)
            ->where('status_id', Prioridad::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->where(function ($query) use ($userId) {
                $query->whereNull('psicologo_use_id')
                      ->orWhere('psicologo_use_id', $userId);
            })->exists();

        if ($existsName) {
            return back()->with('error', 'El nombre de esta prioridad ya está en uso.')->withInput();
        }

        $existsLevel = DB::table('prioridad_atencion')
            ->where('nivel_gravedad_pat', $nivel)
            ->where('status_id', Prioridad::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->where('psicologo_use_id', $userId)
            ->exists();

        if ($existsLevel) {
            return back()->with('error', 'Este nivel de gravedad ya está asignado a otra de tus prioridades. Elimínala primero para liberar el nivel.')->withInput();
        }

        $inactiva = DB::table('prioridad_atencion')
            ->where('nombre_pat', $nombreNormalizado)
            ->where('psicologo_use_id', $userId)
            ->first();

        if ($inactiva) {
            DB::table('prioridad_atencion')->where('id_pat', $inactiva->id_pat)->update([
                'nivel_gravedad_pat' => $nivel,
                'status_id' => Prioridad::STATUS_ACTIVO,
                'deleted_at' => null,
                'updated_id' => $userId,
                'updated_at' => now(),
            ]);
        } else {
            DB::table('prioridad_atencion')->insert([
                'nombre_pat' => $nombreNormalizado,
                'nivel_gravedad_pat' => $nivel,
                'psicologo_use_id' => $userId,
                'status_id' => Prioridad::STATUS_ACTIVO,
                'created_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('admin.psicologia.maestros.prioridades.index')->with('success', 'Prioridad creada correctamente.');
    }

    public function destroy($id)
    {
        $userId = Auth::id();

        $prioridad = DB::table('prioridad_atencion')
            ->where('id_pat', $id)
            ->where('status_id', Prioridad::STATUS_ACTIVO)
            ->whereNull('deleted_at')
            ->first();

        if (!$prioridad) {
            return back()->with('error', 'Prioridad no encontrada.');
        }

        if ($prioridad->psicologo_use_id === null || (int) $prioridad->psicologo_use_id !== (int) $userId) {
            return back()->with('error', 'No puedes eliminar una prioridad base o que no te pertenece.');
        }

        $citasConPrioridad = DB::table('cita_psicologica')
            ->where('psicologo_use_id', $userId)
            ->where('prioridad_texto_cps', $prioridad->nombre_pat)
            ->whereNull('deleted_at')
            ->exists();
            
        if ($citasConPrioridad) {
            return back()->with('error', 'No puedes eliminar esta prioridad porque está asignada a una o más citas pendientes.');
        }

        DB::table('prioridad_atencion')
            ->where('id_pat', $id)
            ->where('psicologo_use_id', $userId)
            ->update([
                'status_id' => Prioridad::STATUS_INACTIVO,
                'updated_id' => $userId,
                'updated_at' => now(),
            ]);

        return redirect()->route('admin.psicologia.maestros.prioridades.index')->with('success', 'Prioridad eliminada. El nivel ha sido liberado.');
    }
}
