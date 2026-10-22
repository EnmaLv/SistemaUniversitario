<?php

namespace App\Http\Controllers;

use App\Models\Perfil;
use Illuminate\Http\Request;
use App\Models\Persona;

class PersonaController extends Controller
{
    public function index(Request $request)
    {
        $perfiles = Perfil::comunidad()->orderBy('id_perfil')->pluck('nombre_perfil', 'id_perfil');

        $query = Persona::deSedeActiva()
            ->with('perfil:id_perfil,nombre_perfil')
            ->select('id_per', 'nombres_per', 'apellidos_per', 'correo_per', 'cedula_per', 'id_perfil')
            ->whereNotIn('id_perfil', Perfil::idsInternos());

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

        return view('admin.configuracion.persona.index', compact('personas', 'perfiles'));
    }

    public function create()
    {
        return view('admin.configuracion.persona.create');
    }

    public function edit($id)
    {
        return view('admin.configuracion.persona.edit', compact('id'));
    }

    public function show($id)
    {
        return view('admin.configuracion.persona.show', compact('id'));
    }
}
