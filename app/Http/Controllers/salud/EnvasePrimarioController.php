<?php

namespace App\Http\Controllers\salud;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\salud\EnvasePrimario;
use Illuminate\Validation\Rule;

class EnvasePrimarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $envases = EnvasePrimario::listar(
            $request->input('buscar'),
            $request->input('estado', 1)
        );
        return view('admin.salud.maestros.envases_primarios.index', compact('envases'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.salud.maestros.envases_primarios.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                'unique:envase_primario,nombre_enp'
            ],
        ], [
            'nombre.unique' => 'Ya existe un envase primario con este nombre',
        ]);
        $fromidreuse = EnvasePrimario::crear($validated);

        $from = $request->input('from');

        if ($from) {
            return redirect($from . '?envase_primario_id=' . $fromidreuse)
                ->with('success', 'Envase primario creado exitosamente.');
        } else {
            return redirect()->route('admin.salud.maestros.envases_primarios.index')
                ->with('success', 'Envase primario creado exitosamente.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(EnvasePrimario $envasePrimario)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $envase = EnvasePrimario::obtenerDatos($id);

        if (!$envase) {
            return redirect()
                ->route('admin.salud.maestros.envases_primarios.index')
                ->with('mensaje', 'Envase primario no encontrado.')
                ->with('icono', 'error');
        }

        return view('admin.salud.maestros.envases_primarios.edit', compact('envase'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EnvasePrimario $envase)
    {
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('envase_primario', 'nombre_enp')->ignore($envase->id_enp, 'id_enp'),
            ],
        ], [
            'nombre.unique' => 'Ya existe un envase primario con este nombre',
        ]);
        EnvasePrimario::actualizar($envase->id_enp, $validated);

        return redirect()
            ->route('admin.salud.maestros.envases_primarios.index')->with('success', 'Envase primario actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {

        EnvasePrimario::eliminar($id);

        return redirect()
            ->route('admin.salud.maestros.envases_primarios.index')
            ->with('success', 'Envase primario eliminado exitosamente.');
    }

    public function activar($id)
    {
        EnvasePrimario::activar($id);
        return redirect()->route('admin.salud.maestros.envases_primarios.index')->with('success', 'Envase primario activado exitosamente.');
    }
}
