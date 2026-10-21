<?php

namespace App\Http\Controllers\Admin\Configuracion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Perfil;
use App\Models\Rol;
use App\Models\Bitacora as BitacoraModelo;
use App\Support\Auditoria\Bitacora;
use App\Support\Auditoria\CambiosPermisos;
use App\Support\Seguridad\Bloqueos;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class EmpleosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(\App\Http\Middleware\RequireMasterKey::class);
    }

    public function index(Request $request)
    {
        $perfiles = Perfil::pluck('nombre_perfil', 'id_perfil')->toArray();
        $roles = Rol::orderBy('nombre')->get();

        $query = Usuario::with(['persona', 'perfil', 'roles']);

        if ($request->filled('perfil')) {
            $query->where('id_perfil', $request->input('perfil'));
        }

        if ($request->filled('rol')) {
            $rolId = $request->input('rol');
            $query->whereHas('roles', function ($q) use ($rolId) {
                $table = $q->getModel()->getTable();
                $q->where("{$table}.id_rol", $rolId);
            });
        }

        $usuarios = $query->orderBy('id_usuario', 'asc')->paginate(10)->withQueryString();

        return view('admin.configuracion.empleados.index', compact('usuarios', 'perfiles', 'roles'));
    }

    public function edit($id)
    {
        $usuario = Usuario::with(['persona', 'perfil', 'roles'])->findOrFail($id);
        $roles = Rol::orderBy('nombre')->get();

        $otherAdminExists = false;
        $adminRol = Rol::where('nombre', 'Administrador')->first();
        if ($adminRol) {
            $otherAdminExists = $adminRol->usuarios()->where('usuario.id_usuario', '!=', $usuario->id_usuario)->exists();
        }

        return view('admin.configuracion.empleados.edit', compact('usuario', 'roles', 'otherAdminExists'));
    }

    public function show($id)
    {
        $usuario = Usuario::with(['persona', 'perfil', 'roles'])->findOrFail($id);
        return view('admin.configuracion.empleados.show', compact('usuario'));
    }

    public function destroy($id)
    {
        $usuario = Usuario::with('roles')->findOrFail($id);
        $auth = Auth::user();

        $adminRol = Rol::where('nombre', 'Administrador')->first();
        if ($auth && $adminRol && $auth->id_usuario == $usuario->id_usuario && $auth->roles->contains('id_rol', $adminRol->id_rol)) {
            return redirect()->route('admin.configuracion.empleados.index')->withErrors(['delete' => 'No puedes eliminarte a ti mismo. Pide a otro Administrador que lo haga.']);
        }

        if ($adminRol && $usuario->roles->contains('id_rol', $adminRol->id_rol)) {
            $adminCount = $adminRol->usuarios()->count();
            if ($adminCount <= 1) {
                return redirect()->route('admin.configuracion.empleados.index')->withErrors(['delete' => 'No se puede eliminar al último Administrador.']);
            }
        }

        $rolesPrevios = $usuario->roles->pluck('id_rol')->all();
        $usuario->roles()->detach();
        CambiosPermisos::rolesUsuario($usuario, ['detached' => $rolesPrevios], 'roles_usuario_retirados');
        $usuario->delete();

        return redirect()->route('admin.configuracion.empleados.index')->with('success', 'Empleado eliminado Exitosamente');
    }

    public function update(Request $request, $id)
    {
        $usuario = Usuario::with(['roles', 'persona'])->findOrFail($id);

        $rules = [
            'username'          => 'required|string|email|max:100|unique:usuario,username,' . $id . ',id_usuario',
            'role'              => 'nullable|integer|exists:rol,id_rol',
            'cedula_per'    => 'required|string|max:20',
            'telefono_per'  => 'required|string|max:20',
            'nombres_per'    => 'required|string|max:100',
            'apellidos_per'  => 'required|string|max:100',
        ];

        if ($request->boolean('modificar_seguridad')) {
            $rules['password']           = 'nullable|string|min:6|confirmed';
            $rules['master_key']         = 'nullable|string|min:6';
            $rules['security_questions'] = 'nullable|array';
        }

        $request->validate($rules);

        if ($usuario->persona) {
            $usuario->persona->update([
                'cedula_per'   => $request->input('cedula_per'),
                'telefono_per' => $request->input('telefono_per'),
                'nombres_per'   => $request->input('nombres_per'),
                'apellidos_per' => $request->input('apellidos_per'),
            ]);
        }

        $usuario->username = $request->input('username');

        if ($request->boolean('modificar_seguridad')) {

            if ($request->filled('password')) {
                $usuario->password = $request->input('password');
            }

            if ($request->has('security_questions')) {
                $currentQuestions = is_string($usuario->security_questions)
                    ? json_decode($usuario->security_questions, true)
                    : ($usuario->security_questions ?? []);

                $newQuestions = $request->input('security_questions');

                $p1 = $currentQuestions['pregunta_1'] ?? '';
                $r1 = $currentQuestions['respuesta_1'] ?? '';
                if ($request->filled('security_questions.respuesta_1')) {
                    $p1 = $newQuestions['pregunta_1'] ?? $p1;
                    $r1 = Hash::make($newQuestions['respuesta_1']);
                }

                $p2 = $currentQuestions['pregunta_2'] ?? '';
                $r2 = $currentQuestions['respuesta_2'] ?? '';
                if ($request->filled('security_questions.respuesta_2')) {
                    $p2 = $newQuestions['pregunta_2'] ?? $p2;
                    $r2 = Hash::make($newQuestions['respuesta_2']);
                }

                $usuario->security_questions = [
                    'pregunta_1'  => $p1,
                    'respuesta_1' => $r1,
                    'pregunta_2'  => $p2,
                    'respuesta_2' => $r2,
                ];
            }
        }

        $role = $request->input('role', null);
        $newRoleIds = $request->has('role') ? ($role ? [$role] : []) : null;

        if (is_array($newRoleIds) && !Auth::user()->esSuperAdmin()) {
            $otorgaSuperAdmin = Rol::whereIn('id_rol', $newRoleIds)->get()->contains(fn ($rol) => $rol->esSuperAdmin());
            $quitaSuperAdmin = $usuario->roles->contains(fn ($rol) => $rol->esSuperAdmin());
            abort_if($otorgaSuperAdmin || $quitaSuperAdmin, 403, 'Solo un superadministrador puede otorgar o retirar roles de superadministrador.');
        }

        $adminRol = Rol::where('nombre', 'Administrador')->first();
        if ($adminRol) {
            $hadAdminBefore = $usuario->roles->contains('id_rol', $adminRol->id_rol);
            $willHaveAdmin = is_array($newRoleIds) ? in_array($adminRol->id_rol, $newRoleIds) : $hadAdminBefore;
            $adminCount = $adminRol->usuarios()->count();

            if ($hadAdminBefore && !$willHaveAdmin && $adminCount <= 1) {
                return back()->withErrors(['role' => 'No se puede quitar el rol de Administrador: existe sólo un Administrador activo.']);
            }

            if ($willHaveAdmin && $request->boolean('modificar_seguridad')) {
                if (!$hadAdminBefore && !$request->filled('master_key')) {
                    return back()->withErrors(['master_key' => 'La Llave Maestra es requerida para otorgar privilegios de Administrador.']);
                }

                if ($request->filled('master_key')) {
                    $usuario->master_key = $request->input('master_key');
                }
            }
        }

        if (is_array($newRoleIds)) {
            CambiosPermisos::rolesUsuario($usuario, $usuario->roles()->sync($newRoleIds));
        }

        \App\Support\Permisos\VersionPermisos::invalidar();

        $usuario->save();

        return redirect()->route('admin.configuracion.empleados.index')->with('success', 'Empleado actualizado exitosamente.');
    }

    public function masterKeyForm()
    {
        return view('admin.configuracion.master_key');
    }

    public function verifyMasterKey(Request $request)
    {
        $request->validate([
            'master_key' => 'required|string',
        ]);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->verifyMasterKey($request->input('master_key'))) {
            session(['master_key_validated' => true]);
            if ((int) $user->intentos_llave_fallidos > 0) {
                $user->intentos_llave_fallidos = 0;
                $user->saveQuietly();
            }
            Bitacora::registrar(accion: 'llave_maestra_correcta', modulo: 'administracion', observacion: 'Acceso a configuración', usuario: $user);

            return redirect()->intended(route('admin.configuracion.empleados.index'));
        }

        Bitacora::registrar(
            accion: 'llave_maestra_incorrecta',
            modulo: 'administracion',
            nivel: BitacoraModelo::NIVEL_ADVERTENCIA,
            observacion: sprintf('Acceso a configuración. Intento %d de %d', (int) $user->intentos_llave_fallidos + 1, (int) config('seguridad.max_intentos_llave', 3)),
            usuario: $user,
        );

        if ($estado = Bloqueos::registrarFalloLlave($user)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => Bloqueos::mensaje($estado)]);
        }

        return back()->withErrors(['master_key' => 'Llave maestra incorrecta']);
    }
}
