<?php

namespace App\Http\Controllers\Admin\Configuracion;

use App\Http\Controllers\Controller;
use App\Models\Modulo;
use App\Models\Permiso;
use App\Models\Rol;
use App\Support\Auditoria\CambiosPermisos;
use App\Support\Permisos\CatalogoPermisos;
use App\Support\Permisos\VersionPermisos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RolesController extends Controller
{
    private const PROTEGIDOS = ['empleado', 'obrero', 'administrador'];

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(\App\Http\Middleware\RequireMasterKey::class);
    }

    public function index(Request $request)
    {
        $query = Rol::query()->withCount('permisos');
        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }
        $roles = $query->orderBy('id_rol', 'desc')->paginate(10)->withQueryString();
        return view('admin.configuracion.roles.index', compact('roles'));
    }

    public function create()
    {
        $modulos = Modulo::where('activo', 1)->orderBy('nombre')->get();
        $grupos  = CatalogoPermisos::agrupado();

        return view('admin.configuracion.roles.create', compact('modulos', 'grupos'));
    }

    public function store(Request $request)
    {
        $request->merge(['slug' => Str::slug($request->input('nombre', ''))]);

        $data = $request->validate($this->reglas(), $this->mensajes());

        DB::transaction(function () use ($data) {
            $rol = Rol::create([
                'nombre'      => $data['nombre'],
                'slug'        => $data['slug'],
                'descripcion' => $data['descripcion'] ?? null,
            ]);

            $sincModulos  = $rol->modulos()->sync($data['modulos'] ?? []);
            $sincPermisos = $rol->permisos()->sync($data['permisos'] ?? []);

            CambiosPermisos::rol($rol, 'rol_permisos_asignados', $sincPermisos, $sincModulos);
        });

        VersionPermisos::invalidar();

        return redirect()->route('admin.configuracion.roles.index')->with('success', 'Rol creado Exitosamente');
    }

    public function edit($id)
    {
        $rol         = Rol::with(['modulos', 'permisos'])->findOrFail($id);
        $modulos     = Modulo::where('activo', 1)->orderBy('nombre')->get();
        $grupos      = CatalogoPermisos::agrupado();
        $isProtected = $this->esProtegido($rol);

        return view('admin.configuracion.roles.edit', compact('rol', 'modulos', 'grupos', 'isProtected'));
    }

    public function update(Request $request, $id)
    {
        $rol = Rol::findOrFail($id);

        if ($this->esProtegido($rol)) {
            return back()->withErrors(['roles' => 'El rol ' . $rol->nombre . ' está protegido y no puede editarse.']);
        }

        $request->merge(['slug' => Str::slug($request->input('nombre', ''))]);

        $data = $request->validate($this->reglas($rol->id_rol), $this->mensajes());

        DB::transaction(function () use ($rol, $data) {
            $rol->update([
                'nombre'      => $data['nombre'],
                'slug'        => $data['slug'],
                'descripcion' => $data['descripcion'] ?? null,
            ]);

            $sincModulos  = $rol->modulos()->sync($data['modulos'] ?? []);
            $sincPermisos = $rol->permisos()->sync($rol->esSuperAdmin() ? Permiso::pluck('id')->all() : ($data['permisos'] ?? []));

            CambiosPermisos::rol($rol, 'rol_permisos_actualizados', $sincPermisos, $sincModulos);
        });

        VersionPermisos::invalidar();

        return redirect()
            ->route('admin.configuracion.roles.index')
            ->with('success', 'Rol actualizado exitosamente');
    }

    public function destroy($id)
    {
        $rol = Rol::findOrFail($id);

        if ($this->esProtegido($rol)) {
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

        DB::transaction(function () use ($rol) {
            $permisos = $rol->permisos()->pluck('permiso.id')->all();
            $modulos  = $rol->modulos()->pluck('modulos.id')->all();

            $rol->permisos()->detach();
            $rol->modulos()->detach();
            $rol->delete();

            CambiosPermisos::rol($rol, 'rol_permisos_retirados', ['detached' => $permisos], ['detached' => $modulos]);
        });

        VersionPermisos::invalidar();

        return redirect()
            ->route('admin.configuracion.roles.index')
            ->with('success', 'Rol eliminado exitosamente.');
    }

    private function esProtegido(Rol $rol): bool
    {
        return in_array(mb_strtolower($rol->nombre ?? ''), self::PROTEGIDOS, true);
    }

    private function reglas(?int $idRol = null): array
    {
        $unicoNombre = 'unique:rol,nombre' . ($idRol ? ",{$idRol},id_rol" : '');
        $unicoSlug   = 'unique:rol,slug' . ($idRol ? ",{$idRol},id_rol" : '');

        return [
            'nombre'      => "required|string|max:255|{$unicoNombre}",
            'slug'        => "required|string|{$unicoSlug}",
            'descripcion' => 'nullable|string',
            'modulos'     => 'nullable|array',
            'modulos.*'   => 'integer|exists:modulos,id',
            'permisos'    => 'nullable|array',
            'permisos.*'  => 'integer|exists:permiso,id',
        ];
    }

    private function mensajes(): array
    {
        return [
            'nombre.required' => 'El nombre del rol es requerido',
            'nombre.unique'   => 'Este nombre de rol ya existe',
            'slug.unique'     => 'Ya existe un rol con un nombre equivalente',
        ];
    }
}
