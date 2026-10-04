<?php

namespace App\Http\Controllers\Admin\Configuracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rol;
use App\Models\Usuario;
use App\Models\Modulo;

class RolesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(\App\Http\Middleware\RequireMasterKey::class);
    }

    public function index(Request $request)
    {
        $query = Rol::query();
        if ($search = $request->get('q')) {
            $query->where('nombre', 'like', "%{$search}%");
        }
        $roles = $query->orderBy('id_rol', 'desc')->paginate(10)->withQueryString();
        return view('admin.configuracion.roles.index', compact('roles'));
    }

    public function create()
    {
        $menu    = config('menu_permissions', []);
        $modulos = Modulo::where('activo', 1)->get();

        return view('admin.configuracion.roles.create', compact('menu', 'modulos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|unique:rol,nombre',
            'descripcion' => 'nullable|string',
            'menu_permissions' => 'nullable|array',
            'modulos' => 'nullable|array',
            'modulos.*' => 'integer'
        ], [
            'nombre.required' => 'El nombre del rol es requerido',
            'nombre.unique' => 'Este nombre de rol ya existe',
        ]);

        $data['menu_permissions'] = array_values($data['menu_permissions'] ?? []);
        $modulosSeleccionados = $data['modulos'] ?? [];

        if (count($modulosSeleccionados) > 1) {
            if (!in_array('admin/modulos/seleccionar', $data['menu_permissions'])) {
                $data['menu_permissions'][] = 'admin/modulos/seleccionar';
            }
        } else {
            $data['menu_permissions'] = array_values(array_diff($data['menu_permissions'], ['admin/modulos/seleccionar']));
        }

        $rol = Rol::create([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'],
            'menu_permissions' => $data['menu_permissions']
        ]);

        $rol->modulos()->sync($modulosSeleccionados);

        return redirect()->route('admin.configuracion.roles.index')->with('success', 'Rol creado Exitosamente');
    }

    public function edit($id)
    {
        $rol     = Rol::findOrFail($id);
        $menu    = config('menu_permissions', []);
        $modulos = Modulo::where('activo', 1)->get();

        $protected = ['Empleado', 'Obrero', 'Administrador'];
        $isProtected = in_array(strtolower($rol->nombre ?? ''), array_map('strtolower', $protected));

        return view('admin.configuracion.roles.edit', compact('rol', 'menu', 'modulos', 'isProtected'));
    }

    public function update(Request $request, $id)
    {
        $rol = Rol::findOrFail($id);
        $protected = ['Empleado', 'Obrero', 'Administrador'];
        if (in_array(strtolower($rol->nombre ?? ''), array_map('strtolower', $protected))) {
            return back()->withErrors(['roles' => 'El rol ' . $rol->nombre . ' está protegido y no puede editarse.']);
        }

        $data = $request->validate([
            'nombre' => 'required|string|unique:rol,nombre,' . $rol->id_rol . ',id_rol',
            'descripcion' => 'nullable|string',
            'menu_permissions' => 'nullable|array',
            'modulos' => 'nullable|array',
            'modulos.*' => 'integer'
        ], [
            'nombre.required' => 'El nombre del rol es requerido',
            'nombre.unique' => 'Este nombre de rol ya existe',
        ]);

        if (($rol->nombre ?? '') === 'Administrador') {
            // El rol admin obtiene TODOS los permisos declarados en el config
            $all = [];
            foreach (config('menu_permissions', []) as $group) {
                foreach (array_keys($group['items'] ?? []) as $key) {
                    $all[] = $key;
                }
            }
            $data['menu_permissions'] = array_values(array_unique($all));
        } else {
            $data['menu_permissions'] = array_values($data['menu_permissions'] ?? []);
            $modulosSeleccionados = $data['modulos'] ?? [];

            if (count($modulosSeleccionados) > 1) {
                if (!in_array('admin/modulos/seleccionar', $data['menu_permissions'])) {
                    $data['menu_permissions'][] = 'admin/modulos/seleccionar';
                }
            } else {
                $data['menu_permissions'] = array_values(
                    array_diff($data['menu_permissions'], ['admin/modulos/seleccionar'])
                );
            }
        }

        $rol->update([
            'nombre'           => $data['nombre'],
            'descripcion'      => $data['descripcion'],
            'menu_permissions' => $data['menu_permissions'],
        ]);

        $rol->modulos()->sync($request->input('modulos', []));

        if (auth()->user()
            && method_exists(auth()->user(), 'roles')
            && auth()->user()->roles->contains('id_rol', $rol->id_rol)) {
            session()->forget(['modulos_permitidos', 'menu_permissions_user', 'es_admin']);
        }

        return redirect()
            ->route('admin.configuracion.roles.index')
            ->with('success', 'Rol actualizado exitosamente');
    }

    public function destroy($id)
    {
        $rol = Rol::findOrFail($id);

        $protected = ['Empleado', 'Obrero', 'Administrador'];
        if (in_array(strtolower($rol->nombre ?? ''), array_map('strtolower', $protected))) {
            return redirect()
                ->route('admin.configuracion.roles.index')
                ->withErrors(['delete' => 'El rol ' . $rol->nombre . ' está protegido y no puede eliminarse.']);
        }

        if (Rol::tieneUsuarios($id)) {
            $cantidad = Rol::contarUsuarios($id);

            return redirect()
                ->route('admin.configuracion.roles.index')
                ->with('error', "No se puede eliminar el rol porque tiene {$cantidad} usuario(s) asociado(s).")
                ->with('icono', 'error');
        }

        $rol->delete();

        return redirect()
            ->route('admin.configuracion.roles.index')
            ->with('success', 'Rol eliminado exitosamente.');
    }
}
