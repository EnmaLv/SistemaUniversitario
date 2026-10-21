<?php

namespace App\Http\Controllers\salud;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\salud\EstadoAnimo;

class EstadoAnimoController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->input('buscar');
        $estados = EstadoAnimo::buscarYPaginar($buscar);
        return view('admin.psicologia.maestros.estado_animos.index', compact('estados', 'buscar'));
    }

    public function create()
    {
        $valoresDisponibles = EstadoAnimo::valoresDisponibles();
        return view('admin.psicologia.maestros.estado_animos.create', compact('valoresDisponibles'));
    }

    public function store(Request $request)
    {
        $valoresDisponibles = EstadoAnimo::valoresDisponibles();
        
        $validated = $request->validate([
            'nombre' => 'required|string|max:50',
            'valor' => 'required|integer|min:1|max:10|in:' . implode(',', $valoresDisponibles),
        ], [
            'valor.in' => 'El valor seleccionado no está disponible o ya fue asignado.',
        ]);

        $nombreNormalizado = trim($validated['nombre']);

        if (EstadoAnimo::nombreExiste($nombreNormalizado)) {
            return back()->with('error', 'El nombre de este estado de ánimo ya está en uso.')->withInput();
        }

        EstadoAnimo::crear([
            'nombre' => $nombreNormalizado,
            'valor' => $validated['valor']
        ]);

        return redirect()->route('admin.psicologia.maestros.estado_animos.index')->with('success', 'Estado de ánimo creado correctamente.');
    }

    public function edit($id)
    {
        $estado = EstadoAnimo::obtenerPorId($id);

        if (!$estado) {
            abort(404);
        }

        $valoresDisponibles = EstadoAnimo::valoresDisponibles($estado->id);
        
        if (!in_array($estado->valor, $valoresDisponibles)) {
            $valoresDisponibles[] = $estado->valor;
        }
        sort($valoresDisponibles);

        return view('admin.psicologia.maestros.estado_animos.edit', compact('estado', 'valoresDisponibles'));
    }

    public function update(Request $request, $id)
    {
        $estado = EstadoAnimo::obtenerPorId($id);

        if (!$estado) {
            return back()->with('error', 'Estado de ánimo no encontrado.');
        }

        $valoresDisponibles = EstadoAnimo::valoresDisponibles($estado->id);
        
        if (!in_array($estado->valor, $valoresDisponibles)) {
            $valoresDisponibles[] = $estado->valor;
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:50',
            'valor' => 'required|integer|min:1|max:10|in:' . implode(',', $valoresDisponibles),
        ], [
            'valor.in' => 'El valor seleccionado no está disponible o ya fue asignado.',
        ]);

        $nombreNormalizado = trim($validated['nombre']);

        $nombreExiste = \Illuminate\Support\Facades\DB::table('estado_animos')
            ->where('status', 1)
            ->where('id', '!=', $id)
            ->whereRaw('LOWER(nombre) = ?', [strtolower($nombreNormalizado)])
            ->exists();

        if ($nombreExiste) {
            return back()->with('error', 'El nombre de este estado de ánimo ya está en uso.')->withInput();
        }

        try {
            EstadoAnimo::actualizar($id, [
                'nombre' => $nombreNormalizado,
                'valor' => $validated['valor']
            ]);

            return redirect()->route('admin.psicologia.maestros.estado_animos.index')->with('success', 'Estado de ánimo actualizado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', \App\Support\Seguridad\MensajeError::de($e))->withInput();
        }
    }

    public function destroy($id)
    {
        $estado = EstadoAnimo::obtenerPorId($id);

        if (!$estado) {
            return back()->with('error', 'Estado de ánimo no encontrado.');
        }

        try {
            EstadoAnimo::eliminar($id);
            return redirect()->route('admin.psicologia.maestros.estado_animos.index')->with('success', 'Estado de ánimo eliminado. El valor ha sido liberado.');
        } catch (\Exception $e) {
            return back()->with('error', \App\Support\Seguridad\MensajeError::de($e));
        }
    }
}
