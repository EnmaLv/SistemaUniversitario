<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Support\Auditoria\Bitacora;
use App\Support\Seguridad\Bloqueos;
use App\Support\Seguridad\ProteccionLogin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuthApiController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        $resultado = ProteccionLogin::intentar(
            (string) $request->input('email'),
            (string) $request->input('password'),
            (string) $request->ip(),
            'api'
        );

        $usuario = $resultado['usuario'];
        if (! $usuario) {
            return response()->json([
                'success' => false,
                'message' => $resultado['mensaje'],
            ], 401);
        }

        Bloqueos::registrarLoginExitoso($usuario, $request);
        Bitacora::registrar(accion: 'login_exitoso', modulo: 'autenticacion', observacion: 'API (token)', usuario: $usuario);

        $usuario->tokens()->where('name', 'flutter-app')->delete();
        $token   = $usuario->createToken('flutter-app')->plainTextToken;
        $persona = $usuario->persona()->first();
        $roles   = $usuario->roles()->select('rol.id_rol', 'rol.nombre_rol', 'rol.slug_rol')->get();
        $modulos = $this->getModulosPermitidos($usuario->id_use);

        return response()->json([
            'success' => true,
            'token'   => $token,
            'usuario' => [
                'id'       => $usuario->id_use,
                'username' => $usuario->username_use,
                'nombre'   => $persona ? trim("{$persona->nombres_per} {$persona->apellidos_per}") : $usuario->username_use,
                'email'    => $persona->correo_per ?? $usuario->username_use,
            ],
            'roles'   => $roles,
            'modulos' => $modulos,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Bitacora::registrar(accion: 'logout', modulo: 'autenticacion', observacion: 'API (token)', usuario: $request->user());
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Sesión cerrada correctamente.']);
    }

    public function me(Request $request): JsonResponse
    {
        $usuario = $request->user();
        $persona = $usuario->persona()->first();
        $roles   = $usuario->roles()->select('rol.id_rol', 'rol.nombre_rol', 'rol.slug_rol')->get();
        $modulos = $this->getModulosPermitidos($usuario->id_use);

        return response()->json([
            'success' => true,
            'usuario' => [
                'id'       => $usuario->id_use,
                'username' => $usuario->username_use,
                'nombre'   => $persona ? trim("{$persona->nombres_per} {$persona->apellidos_per}") : $usuario->username_use,
                'email'    => $persona->correo_per ?? $usuario->username_use,
            ],
            'roles'   => $roles,
            'modulos' => $modulos,
        ]);
    }

    public function modulos(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'modulos' => $this->getModulosPermitidos($request->user()->id_use),
        ]);
    }

    private function getModulosPermitidos(int $userId): array
    {
        $t = Schema::hasTable('modulo') ? 'modulos' : 'modulo';

        // Tablas prefijadas en cada columna para evitar ambigüedad
        $roleIds = DB::table('usuario_rol')
            ->where('usuario_rol.use_id', $userId)
            ->pluck('usuario_rol.rol_id')
            ->toArray();

        if (empty($roleIds)) {
            return [];
        }

        $esAdmin = DB::table('rol')
            ->whereIn('rol.id_rol', $roleIds)
            ->where(function ($q) {
                $q->where('rol.slug_rol', 'administrador')
                    ->orWhere('rol.nombre_rol', 'Administrador');
            })
            ->exists();

        if ($esAdmin) {
            return DB::table($t)
                ->where("$t.activo", 1)
                ->select("$t.id", "$t.key", "$t.nombre", "$t.descripcion")
                ->get()->toArray();
        }

        return DB::table('rol_modulo')
            ->join($t, "$t.id", '=', 'rol_modulo.mod_id')
            ->whereIn('rol_modulo.rol_id', $roleIds)
            ->where("$t.activo", 1)
            ->distinct()
            ->select("$t.id", "$t.key", "$t.nombre", "$t.descripcion")
            ->get()->toArray();
    }
}
