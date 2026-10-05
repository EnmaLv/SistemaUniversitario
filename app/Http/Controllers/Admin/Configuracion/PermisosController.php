<?php

namespace App\Http\Controllers\Admin\Configuracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;

class PermisosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(\App\Http\Middleware\RequireMasterKey::class);
    }

    public function index(Request $request)
    {
        $search = $request->input('q');

        $usuarios = Usuario::with(['persona', 'perfil', 'roles'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                    ->orWhereHas('persona', function ($subQ) use ($search) {
                        $subQ->where('nombre_persona', 'like', "%{$search}%")
                            ->orWhere('apellido_persona', 'like', "%{$search}%");
                    });
                });
            })
            ->orderBy('id_usuario', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.configuracion.permisos.index', compact('usuarios'));
    }

    public function edit($id)
    {
        $usuario = Usuario::with(['persona', 'perfil', 'roles'])->findOrFail($id);
        $auth = auth()->user();

        if ($auth && $auth->id_usuario == $usuario->id_usuario
            && $auth->roles->contains('nombre', 'Administrador')) {
            return redirect()
                ->route('admin.configuracion.permisos.index')
                ->withErrors(['permisos' => 'No puedes editar tus propios permisos. Pide a otro Administrador que lo haga.']);
        }

        /* ─── Módulos del sistema ─── */
        $modulos = \App\Models\Modulo::where('activo', true)->orderBy('nombre')->get();

        /* ─── Extra permissions guardados ─── */
        $extra = is_string($usuario->extra_permissions)
            ? json_decode($usuario->extra_permissions, true)
            : ($usuario->extra_permissions ?? []);

        $allow        = array_values($extra['allow']   ?? []);
        $deny         = array_values($extra['deny']    ?? []);
        $modulosExtra = array_values(array_map('intval', $extra['modulos'] ?? []));

        /* ─── Permisos / módulos que vienen del rol ─── */
        $rolePerms   = [];
        $roleModules = [];
        foreach ($usuario->roles as $r) {
            $perms = $r->menu_permissions ?? [];
            if (is_array($perms)) $rolePerms = array_merge($rolePerms, $perms);

            $mods = $r->modulos ? $r->modulos->pluck('id')->toArray() : [];
            $roleModules = array_merge($roleModules, $mods);
        }
        $rolePerms   = array_values(array_unique($rolePerms));
        $roleModules = array_values(array_unique($roleModules));

        /* ─── Grupos de permisos (mismo config que usa el form de roles) ─── */
        $grupos = config('menu_permissions', []);

        return view('admin.configuracion.permisos.edit', compact(
            'usuario',
            'grupos',
            'allow',
            'deny',
            'rolePerms',
            'modulos',
            'roleModules',
            'modulosExtra'
        ));
    }

    public function update(Request $request, $id)
    {
        $usuario = Usuario::with('roles')->findOrFail($id);
        $auth = auth()->user();

        if ($auth && $auth->id_usuario == $usuario->id_usuario
            && $auth->roles->contains('nombre', 'Administrador')) {
            return back()->withErrors(['permisos' => 'No puedes modificar tus propios permisos. Pide a otro Administrador que lo haga.']);
        }

        $data = $request->validate([
            'allow'   => 'nullable|array',
            'deny'    => 'nullable|array',
            'modulos' => 'nullable|array',
        ]);

        $allow        = array_values(array_unique($data['allow']   ?? []));
        $deny         = array_values(array_unique($data['deny']    ?? []));
        $modulosExtra = array_values(array_unique(array_map('intval', $data['modulos'] ?? [])));

        $usuario->extra_permissions = json_encode([
            'allow'   => $allow,
            'deny'    => $deny,
            'modulos' => $modulosExtra,
        ]);
        $usuario->save();

        return redirect()
            ->route('admin.configuracion.permisos.index')
            ->with('success', 'Permisos y módulos especiales actualizados con éxito.');
    }
}
