<?php

namespace App\Http\Controllers\salud;

use App\Http\Controllers\Controller;
use App\Models\salud\AvanceSesion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AvanceSesionController extends Controller
{
    private function verificarAcceso(): void
    {
        /** @var Usuario $user */
        $user = Auth::user();
        $rolesPermitidos = 'psicologo';

        if (!$user || !$user->tieneRol($rolesPermitidos)) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }
    }

    public function index(Request $request)
    {
        $this->verificarAcceso();

        $search = $request->input('search');
        $avances = AvanceSesion::obtenerPaginadoPorPsicologo(Auth::id(), $search, 6);

        if ($request->ajax()) {
            return view('admin.psicologia.maestros.avances_sesion.partials.table', compact('avances'))->render();
        }

        return view('admin.psicologia.maestros.avances_sesion.index', compact('avances', 'search'));
    }

    public function create()
    {
        $this->verificarAcceso();

        return view('admin.psicologia.maestros.avances_sesion.create');
    }

    public function store(Request $request)
    {
        $this->verificarAcceso();

        $request->validate([
            'nombre' => 'required|string|max:255',
            'valor' => 'required|integer|min:1|max:10',
            'descripcion' => 'nullable|string',
        ]);

        try {
            $datos = $request->only(['nombre', 'valor', 'descripcion']);
            AvanceSesion::crear(Auth::id(), $datos);

            return redirect()->route('admin.psicologia.maestros.avances_sesion.index')
                ->with('success', 'Avance de sesión registrado con éxito.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $this->verificarAcceso();

        $avance = DB::table('avances_sesion')->where('id', $id)->first();
        if (!$avance) {
            abort(404);
        }

        return view('admin.psicologia.maestros.avances_sesion.edit', compact('avance'));
    }

    public function update(Request $request, $id)
    {
        $this->verificarAcceso();

        $request->validate([
            'nombre' => 'required|string|max:255',
            'valor' => 'required|integer|min:1|max:10',
            'descripcion' => 'nullable|string',
        ]);

        try {
            $datos = $request->only(['nombre', 'valor', 'descripcion']);
            AvanceSesion::actualizar($id, Auth::id(), $datos);

            return redirect()->route('admin.psicologia.maestros.avances_sesion.index')
                ->with('success', 'Avance actualizado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $this->verificarAcceso();

        try {
            AvanceSesion::eliminar($id, Auth::id());

            return redirect()->route('admin.psicologia.maestros.avances_sesion.index')
                ->with('success', 'Avance de sesión eliminado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}