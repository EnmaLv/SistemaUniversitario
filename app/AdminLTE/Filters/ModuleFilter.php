<?php

namespace App\AdminLTE\Filters;

use App\Models\Usuario;
use App\Support\Permisos\VersionPermisos;
use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModuleFilter implements FilterInterface
{
    private const SESSION_VERSION = 3;

    public function transform($item)
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        $userId = $user->id_usuario ?? $user->id ?? null;
        if (! $userId) {
            return false;
        }

        $this->asegurarSesion($userId);

        $permitidos      = session('modulos_permitidos', []);
        $menuPermissions = session('menu_permissions_user', []);
        $esAdmin         = session('es_admin', false);
        $moduloActivo    = session('modulo_activo', null);

        if (isset($item['key']) && ! empty($item['key'])) {
            if (! $esAdmin && ! in_array($item['key'], $menuPermissions)) {
                return false;
            }
        }

        if (isset($item['module']) && ! empty($item['module'])) {
            if (is_null($moduloActivo)) {
                return false;
            }
            if ($item['module'] !== $moduloActivo) {
                return false;
            }
            if (! in_array($item['module'], $permitidos)) {
                return false;
            }
        }

        return $item;
    }

    public function resolveInitialRoute($userId): string
    {
        session()->forget([
            'modulos_permitidos',
            'menu_permissions_user',
            'es_admin',
            'modulo_activo',
            'permisos_usuario_id',
            'permisos_version',
            'permisos_global',
        ]);

        $this->inicializarSesion($userId);

        $permitidos = session('modulos_permitidos', []);

        if (count($permitidos) === 1) {
            session(['modulo_activo' => $permitidos[0]]);
        }

        return route('home');
    }

    public function asegurarSesion($userId): void
    {
        $cambioUsuario = (int) session('permisos_usuario_id') !== (int) $userId;

        if ($cambioUsuario) {
            session()->forget('modulo_activo');
        }

        if (
            is_null(session('modulos_permitidos'))
            || is_null(session('menu_permissions_user'))
            || $cambioUsuario
            || (int) session('permisos_version') !== self::SESSION_VERSION
            || (int) session('permisos_global') !== VersionPermisos::actual()
        ) {
            $moduloPrevio = $cambioUsuario ? null : session('modulo_activo');

            $this->inicializarSesion($userId);

            $permitidos = session('modulos_permitidos', []);

            if ($moduloPrevio && in_array($moduloPrevio, $permitidos, true)) {
                session(['modulo_activo' => $moduloPrevio]);
            } elseif (count($permitidos) === 1) {
                session(['modulo_activo' => $permitidos[0]]);
            } else {
                session()->forget('modulo_activo');
            }
        }
    }

    public function inicializarSesion($userId)
    {
        $modulesTable = Schema::hasTable('modulos')
            ? 'modulos'
            : (Schema::hasTable('modulo') ? 'modulo' : null);

        $usuario = Usuario::find($userId);

        if (! $modulesTable || ! $usuario) {
            $this->guardarSesion($userId, [], [], false);
            return;
        }

        $esAdmin = $usuario->esSuperAdmin();

        $roleIds = DB::table('rol_usuario')
            ->join('rol', 'rol.id_rol', '=', 'rol_usuario.id_rol')
            ->where('rol_usuario.id_usuario', $userId)
            ->whereNull('rol.deleted_at')
            ->pluck('rol.id_rol')
            ->all();

        if ($esAdmin) {
            $permitidos = DB::table($modulesTable)
                ->where('activo', 1)
                ->pluck('key')
                ->toArray();
        } else {
            $deRol = empty($roleIds) ? [] : DB::table('rol_modulo')
                ->join($modulesTable, $modulesTable . '.id', '=', 'rol_modulo.modulo_id')
                ->whereIn('rol_modulo.rol_id', $roleIds)
                ->where($modulesTable . '.activo', 1)
                ->pluck($modulesTable . '.key')
                ->toArray();

            $extra = Schema::hasTable('usuario_modulo')
                ? DB::table('usuario_modulo')
                    ->join($modulesTable, $modulesTable . '.id', '=', 'usuario_modulo.modulo_id')
                    ->where('usuario_modulo.usuario_id', $userId)
                    ->where($modulesTable . '.activo', 1)
                    ->pluck($modulesTable . '.key')
                    ->toArray()
                : [];

            $permitidos = array_values(array_unique(array_merge($deRol, $extra)));
        }

        $this->guardarSesion($userId, $permitidos, $usuario->vistasVisibles(), $esAdmin);
    }

    private function guardarSesion($userId, array $permitidos, array $vistas, bool $esAdmin): void
    {
        session([
            'modulos_permitidos'    => $permitidos,
            'menu_permissions_user' => $vistas,
            'es_admin'              => $esAdmin,
            'permisos_usuario_id'   => $userId,
            'permisos_version'      => self::SESSION_VERSION,
            'permisos_global'       => VersionPermisos::actual(),
        ]);
    }
}
