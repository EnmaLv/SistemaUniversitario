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
        if ($auth && $auth->id_usuario == $usuario->id_usuario && $auth->roles->contains('nombre', 'Administrador')) {
            return redirect()->route('admin.configuracion.permisos.index')->withErrors(['permisos' => 'No puedes editar tus propios permisos. Pide a otro Administrador que lo haga.']);
        }
        $menu = config('menu_routes', []);

        $extra = is_string($usuario->extra_permissions) ? json_decode($usuario->extra_permissions, true) : ($usuario->extra_permissions ?? []);
        $allow = $extra['allow'] ?? [];
        $deny = $extra['deny'] ?? [];
        $modulosExtra = $extra['modulos'] ?? [];
        $modulos = \App\Models\Modulo::all();

        $rolePerms = [];
        $roleModules = [];
        foreach ($usuario->roles as $r) {
            $perms = $r->menu_permissions ?? [];
            if (is_array($perms)) $rolePerms = array_merge($rolePerms, $perms);

            $mods = $r->modulos ? $r->modulos->pluck('id')->toArray() : [];
            $roleModules = array_merge($roleModules, $mods);
        }
        $rolePerms = array_values(array_unique($rolePerms));
        $roleModules = array_values(array_unique($roleModules));

        $keyToPatterns = [];
        $collector = function ($items) use (&$collector, &$keyToPatterns) {
            foreach ($items as $it) {
                if (isset($it['submenu']) && is_array($it['submenu'])) {
                    $collector($it['submenu']);
                }
                $key = $it['key'] ?? null;
                $patterns = [];
                if (!empty($it['active']) && is_array($it['active'])) {
                    $patterns = array_merge($patterns, $it['active']);
                }
                if (!empty($it['url'])) {
                    $patterns[] = ltrim($it['url'], '/');
                }
                if (!empty($it['route'])) {
                    $patterns[] = $it['route'];
                }
                if ($key && ! empty($patterns)) {
                    $keyToPatterns[$key] = array_values(array_unique($patterns));
                }
            }
        };
        $collector($menu);

        $expand = function ($arr) use ($keyToPatterns) {
            $out = [];
            foreach ($arr as $p) {
                if (isset($keyToPatterns[$p])) {
                    foreach ($keyToPatterns[$p] as $pat) $out[] = $pat;
                } else {
                    $out[] = $p;
                }
            }
            return array_values(array_unique($out));
        };

        $rolePatterns = $expand($rolePerms);
        $denyPatterns = $expand($deny);
        $allowPatterns = $expand($allow);

        $effective = array_values(array_unique(array_merge(array_values(array_diff($rolePatterns, $denyPatterns)), $allowPatterns)));

        return view('admin.configuracion.permisos.edit', compact(
            'usuario',
            'menu',
            'allow',
            'effective',
            'rolePerms',
            'rolePatterns',
            'keyToPatterns',
            'modulos',
            'roleModules',
            'modulosExtra'
        ));
    }

    public function update(Request $request, $id)
    {
        $usuario = Usuario::with('roles')->findOrFail($id);
        $auth = auth()->user();
        if ($auth && $auth->id_usuario == $usuario->id_usuario && $auth->roles->contains('nombre', 'Administrador')) {
            return back()->withErrors(['permisos' => 'No puedes modificar tus propios permisos. Pide a otro Administrador que lo haga.']);
        }

        $data = $request->validate([
            'allow' => 'nullable|array',
            'deny' => 'nullable|array',
            'modulos' => 'nullable|array',
        ]);

        $roleModules = [];
        $rolePerms = [];
        foreach ($usuario->roles as $r) {
            $mods = $r->modulos ? $r->modulos->pluck('id')->toArray() : [];
            $roleModules = array_merge($roleModules, $mods);

            $perms = $r->menu_permissions ?? [];
            if (is_array($perms)) $rolePerms = array_merge($rolePerms, $perms);
        }
        $roleModules = array_values(array_unique($roleModules));
        $rolePerms = array_values(array_unique($rolePerms));

        $modulosExtra = array_map('intval', array_values($data['modulos'] ?? []));

        $totalModulosCount = count(array_unique(array_merge($roleModules, $modulosExtra)));

        $allow = array_values($data['allow'] ?? []);
        $deny = array_values($data['deny'] ?? []);
        $selectorKey = 'admin/modulos/seleccionar';

        if ($totalModulosCount > 1) {
            if (in_array($selectorKey, $rolePerms)) {
                $deny = array_values(array_diff($deny, [$selectorKey]));
            } else {
                if (!in_array($selectorKey, $allow)) {
                    $allow[] = $selectorKey;
                }
            }
        } else {
            if (in_array($selectorKey, $rolePerms)) {
                if (!in_array($selectorKey, $deny)) {
                    $deny[] = $selectorKey;
                }
            } else {
                $allow = array_values(array_diff($allow, [$selectorKey]));
            }
        }

        $extra = [
            'allow' => $allow,
            'deny' => $deny,
            'modulos' => $modulosExtra,
        ];

        $usuario->extra_permissions = json_encode($extra);
        $usuario->save();

        return redirect()->route('admin.configuracion.permisos.index')->with('success', 'Permisos y Módulos especiales actualizados con éxito.');
    }
}
