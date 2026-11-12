<?php

namespace App\Http\Controllers;

use App\Models\Perfil;
use Illuminate\Http\Request;
use App\Models\Persona;

class PersonaController extends Controller
{
    public function index(Request $request)
    {
        $esSaludPsico = Persona::esContextoSaludPsicologia();

        $perfilesQuery = $esSaludPsico ? Perfil::saludPsicologia() : Perfil::comunidad();
        $perfiles = $perfilesQuery->orderBy('id_perfil')->pluck('nombre_perfil', 'id_perfil');

        $query = Persona::deSedeActiva()
            ->with('perfil:id_perfil,nombre_perfil')
            ->select('id_per', 'nombres_per', 'apellidos_per', 'correo_per', 'cedula_per', 'id_perfil')
            ->whereNotIn('id_perfil', Perfil::idsInternos());

        if ($esSaludPsico) {
            $query->whereIn('id_perfil', Perfil::idsPermitidosSaludPsicologia());
        }

        if ($request->filled('perfil') && $perfiles->has((int) $request->input('perfil'))) {
            $query->where('id_perfil', (int) $request->input('perfil'));
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;

            $query->where(function ($q) use ($buscar) {
                $q->where('nombres_per', 'like', "%{$buscar}%")
                ->orWhere('apellidos_per', 'like', "%{$buscar}%")
                ->orWhere('cedula_per', 'like', "%{$buscar}%");
            });
        }

        $personas = $query->orderBy('apellidos_per')->orderBy('nombres_per')->paginate(10)->withQueryString();

        return view('admin.configuracion.persona.index', compact('personas', 'perfiles', 'esSaludPsico'));
    }

    public function create()
    {
        return view('admin.configuracion.persona.create');
    }

    public function edit($id)
    {
        $persona = Persona::findOrFail($id);

        if (Persona::esContextoSaludPsicologia() && Perfil::esPerfilProhibidoSaludPsicologia($persona->id_perfil)) {
            abort(403, 'No tiene permiso para editar estudiantes, docentes o personal administrativo desde este módulo.');
        }

        return view('admin.configuracion.persona.edit', compact('id'));
    }

    public function show($id)
    {
        $persona = Persona::findOrFail($id);

        if (Persona::esContextoSaludPsicologia() && Perfil::esPerfilProhibidoSaludPsicologia($persona->id_perfil)) {
            abort(403, 'No tiene permiso para consultar datos de estudiantes, docentes o personal administrativo desde este módulo.');
        }

        return view('admin.configuracion.persona.show', compact('id'));
    }
}
