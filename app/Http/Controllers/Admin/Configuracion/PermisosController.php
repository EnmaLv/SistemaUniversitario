<?php

namespace App\Http\Controllers\Admin\Configuracion;

use App\Http\Controllers\Controller;
use App\Models\Modulo;
use App\Models\Usuario;
use App\Support\Auditoria\CambiosPermisos;
use App\Support\Permisos\CatalogoPermisos;
use App\Support\Permisos\VersionPermisos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            ->withCount('permisosDirectos')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                    ->orWhereHas('persona', function ($subQ) use ($search) {
                        $subQ->where('nombres_per', 'like', "%{$search}%")
                            ->orWhere('apellidos_per', 'like', "%{$search}%");
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

        if ($this->esAutoEdicionDeAdmin($usuario)) {
            return redirect()
                ->route('admin.configuracion.permisos.index')
                ->withErrors(['permisos' => 'No puedes editar tus propios permisos. Pide a otro Administrador que lo haga.']);
        }

        $roleIds = $usuario->roles->pluck('id_rol')->all();

        $modulos      = Modulo::where('activo', true)->orderBy('nombre')->get();
        $roleModules  = DB::table('rol_modulo')->whereIn('rol_id', $roleIds)->pluck('modulo_id')->unique()->values()->all();
        $modulosExtra = DB::table('usuario_modulo')->where('usuario_id', $usuario->id_usuario)->pluck('modulo_id')->all();

        $grupos  = CatalogoPermisos::agrupado();
        $deRol   = DB::table('rol_permiso')->whereIn('rol_id', $roleIds)->pluck('permiso_id')->unique()->values()->all();

        $directos = DB::table('usuario_permiso')->where('usuario_id', $usuario->id_usuario)->get();
        $grants   = $directos->where('tipo', 'grant')->pluck('permiso_id')->all();
        $revokes  = $directos->where('tipo', 'revoke')->pluck('permiso_id')->all();

        $vigencia = [
            'fecha_inicio' => $directos->pluck('fecha_inicio')->filter()->first(),
            'fecha_fin'    => $directos->pluck('fecha_fin')->filter()->first(),
        ];

        $esSuperAdmin = $usuario->esSuperAdmin();

        return view('admin.configuracion.permisos.edit', compact(
            'usuario',
            'grupos',
            'deRol',
            'grants',
            'revokes',
            'vigencia',
            'modulos',
            'roleModules',
            'modulosExtra',
            'esSuperAdmin'
        ));
    }

    public function update(Request $request, $id)
    {
        $usuario = Usuario::with('roles')->findOrFail($id);

        if ($this->esAutoEdicionDeAdmin($usuario)) {
            return back()->withErrors(['permisos' => 'No puedes modificar tus propios permisos. Pide a otro Administrador que lo haga.']);
        }

        $data = $request->validate([
            'grant'        => 'nullable|array',
            'grant.*'      => 'integer|exists:permiso,id',
            'revoke'       => 'nullable|array',
            'revoke.*'     => 'integer|exists:permiso,id',
            'modulos'      => 'nullable|array',
            'modulos.*'    => 'integer|exists:modulos,id',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin'    => 'nullable|date|after_or_equal:fecha_inicio',
        ], [
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
        ]);

        $revoke  = array_values(array_unique(array_map('intval', $data['revoke'] ?? [])));
        $grant   = array_values(array_diff(array_unique(array_map('intval', $data['grant'] ?? [])), $revoke));
        $modulos = array_values(array_unique(array_map('intval', $data['modulos'] ?? [])));

        $asignadoPor = auth()->id();
        $ahora       = now();
        $base        = [
            'usuario_id'   => $usuario->id_usuario,
            'fecha_inicio' => $data['fecha_inicio'] ?? null,
            'fecha_fin'    => $data['fecha_fin'] ?? null,
            'asignado_por' => $asignadoPor,
            'created_at'   => $ahora,
            'updated_at'   => $ahora,
        ];

        DB::transaction(function () use ($usuario, $grant, $revoke, $modulos, $base, $asignadoPor, $ahora) {
            $previos = DB::table('usuario_permiso')->where('usuario_id', $usuario->id_usuario)->get();
            $antes = [
                'grant'    => $previos->where('tipo', 'grant')->pluck('permiso_id')->map('intval')->all(),
                'revoke'   => $previos->where('tipo', 'revoke')->pluck('permiso_id')->map('intval')->all(),
                'modulos'  => DB::table('usuario_modulo')->where('usuario_id', $usuario->id_usuario)->pluck('modulo_id')->map('intval')->all(),
                'vigencia' => [$previos->pluck('fecha_inicio')->filter()->first(), $previos->pluck('fecha_fin')->filter()->first()],
            ];

            DB::table('usuario_permiso')->where('usuario_id', $usuario->id_usuario)->delete();

            $filas = array_merge(
                array_map(fn ($permisoId) => $base + ['permiso_id' => $permisoId, 'tipo' => 'grant'], $grant),
                array_map(fn ($permisoId) => $base + ['permiso_id' => $permisoId, 'tipo' => 'revoke'], $revoke)
            );

            if ($filas) {
                DB::table('usuario_permiso')->insert($filas);
            }

            DB::table('usuario_modulo')->where('usuario_id', $usuario->id_usuario)->delete();

            if ($modulos) {
                DB::table('usuario_modulo')->insert(array_map(fn ($moduloId) => [
                    'usuario_id'   => $usuario->id_usuario,
                    'modulo_id'    => $moduloId,
                    'asignado_por' => $asignadoPor,
                    'created_at'   => $ahora,
                    'updated_at'   => $ahora,
                ], $modulos));
            }

            CambiosPermisos::permisosUsuario($usuario, $antes, [
                'grant'    => $grant,
                'revoke'   => $revoke,
                'modulos'  => $modulos,
                'vigencia' => ($grant || $revoke) ? [$base['fecha_inicio'], $base['fecha_fin']] : [null, null],
            ]);
        });

        VersionPermisos::invalidar();

        return redirect()
            ->route('admin.configuracion.permisos.index')
            ->with('success', 'Permisos y módulos especiales actualizados con éxito.');
    }

    private function esAutoEdicionDeAdmin(Usuario $usuario): bool
    {
        $auth = auth()->user();

        return $auth
            && $auth->id_usuario == $usuario->id_usuario
            && $auth->esSuperAdmin();
    }
}
