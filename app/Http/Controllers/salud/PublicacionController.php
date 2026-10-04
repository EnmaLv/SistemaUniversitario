<?php

namespace App\Http\Controllers\salud;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\salud\Publicacion;
use App\Models\salud\Notification;
use App\Models\Usuario;
use Illuminate\Support\Str;

class PublicacionController extends Controller
{
    public function index()
    {
        /** @var Usuario $user */
        $user = Auth::user();
        if (!$user || !$user->tieneRol('psicologo')) {
            return redirect()->back()->with('error', 'Acceso no autorizado.');
        }

        $publicaciones = Publicacion::byPsicologo($user->id_usuario);
        return view('admin.psicologia.maestros.publicaciones.index', compact('publicaciones'));
    }

    public function mural()
    {
        $publicaciones = Publicacion::forPacientes();
        return view('admin.psicologia.maestros.publicaciones.mural', compact('publicaciones'));
    }

    public function create()
    {
        /** @var Usuario $user */
        $user = Auth::user();
        if (!$user || !$user->tieneRol('psicologo')) {
            return redirect()->back()->with('error', 'Acceso no autorizado.');
        }

        return view('admin.psicologia.maestros.publicaciones.create');
    }

    public function store(Request $request)
    {
        /** @var Usuario $user */
        $user = Auth::user();
        if (!$user || !$user->tieneRol('psicologo')) {
            return redirect()->back()->with('error', 'Acceso no autorizado.');
        }

        $request->validate([
            'titulo' => 'required|string|max:255',
            'contenido' => 'nullable|string',
            'alcance' => 'required|in:todos,mis_pacientes',
            'tipo' => 'required|in:texto,color,imagen',
            'color_fondo' => 'nullable|string',
            'imagen' => 'nullable|image|max:2048'
        ]);

        $mediaPath = null;
        if ($request->tipo === 'imagen' && $request->hasFile('imagen')) {
            $mediaPath = $request->file('imagen')->store('publicaciones', 'public');
        }

        Publicacion::create([
            'psicologo_id' => $user->id_usuario,
            'titulo' => $request->titulo,
            'contenido' => $request->contenido,
            'alcance' => $request->alcance,
            'tipo' => $request->tipo,
            'color_fondo' => $request->tipo === 'color' ? $request->color_fondo : null,
            'media_path' => $mediaPath
        ]);

        $mensaje = "El psicólogo {$user->persona->nombre_persona} ha publicado un nuevo aviso: {$request->titulo}";

        $pacientes = Usuario::all()->filter(fn($usuario) => $usuario->tieneRol(['paciente']));

        $notificaciones = [];
        foreach ($pacientes as $paciente) {
            $notificaciones[] = [
                'id' => Str::uuid()->toString(),
                'type' => 'App\\Notifications\\NuevoAvisoNotification',
                'notifiable_type' => Usuario::class,
                'notifiable_id' => $paciente->id_usuario,
                'data' => json_encode([
                    'type_id' => 'nuevo_aviso',
                    'body' => $mensaje,
                    'url' => route('admin.psicologia.maestros.publicaciones.mural')
                ]),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($notificaciones)) {
            Notification::insert($notificaciones);
        }

        return redirect()->route('admin.psicologia.maestros.publicaciones.index')->with('success', 'Publicación creada exitosamente.');
    }

    public function edit($id)
    {
        /** @var Usuario $user */
        $user = Auth::user();
        $publicacion = Publicacion::findById($id);

        if (!$publicacion || ($publicacion->psicologo_id != $user->id_usuario && !$user->tieneRol('psicologo'))) {
            return redirect()->route('admin.psicologia.maestros.publicaciones.index')->with('error', 'Acceso denegado.');
        }

        return view('admin.psicologia.maestros.publicaciones.edit', compact('publicacion'));
    }

    public function update(Request $request, $id)
    {
        /** @var Usuario $user */
        $user = Auth::user();
        $publicacion = Publicacion::findById($id);

        if (!$publicacion || ($publicacion->psicologo_id != $user->id_usuario && !$user->tieneRol(['psicologo']))) {
            return redirect()->route('admin.psicologia.maestros.publicaciones.index')->with('error', 'Acceso denegado.');
        }

        $request->validate([
            'titulo' => 'required|string|max:255',
            'contenido' => 'nullable|string',
            'alcance' => 'required|in:todos,mis_pacientes',
        ]);

        Publicacion::where('id', $id)->update([
            'titulo' => $request->titulo,
            'contenido' => $request->contenido ?? '',
            'alcance' => $request->alcance,
        ]);

        return redirect()->route('admin.psicologia.maestros.publicaciones.index')->with('success', 'Publicación actualizada exitosamente.');
    }

    public function destroy($id)
    {
        /** @var Usuario $user */
        $user = Auth::user();
        $publicacion = Publicacion::findById($id);

        if (!$publicacion || ($publicacion->psicologo_id != $user->id_usuario && !$user->tieneRol('psicologo'))) {
            return redirect()->route('admin.psicologia.maestros.publicaciones.index')->with('error', 'Acceso denegado.');
        }

        Publicacion::desactivar($id);

        return redirect()->route('admin.psicologia.maestros.publicaciones.index')->with('success', 'Publicación eliminada.');
    }
}
