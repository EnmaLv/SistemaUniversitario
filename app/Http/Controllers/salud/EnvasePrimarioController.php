<?php

namespace App\Http\Controllers\salud;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\salud\EnvasePrimario;
use App\Models\TipoProducto;
use Illuminate\Support\Facades\DB;
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
            $request->input('estado', 1),
            $this->tipoId()
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
        $nombre = trim((string) $request->input('nombre', ''));
        $existente = $nombre === '' ? null : DB::table('envase_primario')
            ->whereNull('deleted_at')
            ->where(DB::raw('LOWER(nombre_enp)'), mb_strtolower($nombre))
            ->value('id_enp');

        if ($existente !== null && ! $this->esDeMedicamentos((int) $existente)) {
            EnvasePrimario::vincular((int) $existente, $this->tipoId());

            return redirect()->route('admin.salud.maestros.envases_primarios.index')
                ->with('success', 'Ese envase ya existía en otra área; ahora también aparece en medicamentos.');
        }

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
        $fromidreuse = DB::transaction(function () use ($validated) {
            $id = EnvasePrimario::crear($validated);
            EnvasePrimario::vincular((int) $id, $this->tipoId());

            return $id;
        });

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
        abort_unless($this->esDeMedicamentos((int) $envase->id_enp), 404);

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
        abort_unless($this->esDeMedicamentos((int) $id), 404);

        EnvasePrimario::eliminar($id);

        return redirect()
            ->route('admin.salud.maestros.envases_primarios.index')
            ->with('success', 'Envase primario eliminado exitosamente.');
    }

    public function activar($id)
    {
        abort_unless($this->esDeMedicamentos((int) $id), 404);

        EnvasePrimario::activar($id);
        return redirect()->route('admin.salud.maestros.envases_primarios.index')->with('success', 'Envase primario activado exitosamente.');
    }

    private function tipoId(): int
    {
        $id = TipoProducto::idPorNombre(TipoProducto::MEDICAMENTO);

        abort_if($id === null, 404, 'El tipo de producto "' . TipoProducto::MEDICAMENTO . '" no está registrado.');

        return $id;
    }

    private function esDeMedicamentos(int $envaseId): bool
    {
        return DB::table('envase_tipo_producto')
            ->where('enp_id', $envaseId)
            ->where('tpr_id', $this->tipoId())
            ->whereNull('deleted_at')
            ->exists();
    }
}
