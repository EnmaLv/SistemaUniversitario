<?php

namespace App\AdminLTE\Filters;

use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModuleFilter implements FilterInterface
{
    private const SESSION_VERSION = 2;

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
        ) {
            session()->forget('modulo_activo');
            $this->inicializarSesion($userId);
        }
    }

    public function inicializarSesion($userId)
    {
        $modulesTable = Schema::hasTable('modulos')
            ? 'modulos'
            : (Schema::hasTable('modulo') ? 'modulo' : null);

        if (! $modulesTable) {
            session([
                'modulos_permitidos'    => [],
                'menu_permissions_user' => [],
                'es_admin'              => false,
                'permisos_usuario_id'   => $userId,
                'permisos_version'      => self::SESSION_VERSION,
            ]);
            return;
        }

        $roles = DB::table('rol_usuario')
            ->join('rol', 'rol.id_rol', '=', 'rol_usuario.id_rol')
            ->where('rol_usuario.id_usuario', $userId)
            ->whereNull('rol.deleted_at')
            ->get();

        if ($roles->isEmpty()) {
            session([
                'modulos_permitidos'    => [],
                'menu_permissions_user' => [],
                'es_admin'              => false,
                'permisos_usuario_id'   => $userId,
                'permisos_version'      => self::SESSION_VERSION,
            ]);
            return;
        }

        $roleIds = $roles->pluck('id_rol')->toArray();

        $esAdmin = $roles->contains(function ($rol) {
            $nombreLower = strtolower($rol->nombre);
            $slugLower   = strtolower($rol->slug ?? '');
            return in_array($nombreLower, ['administrador', 'secretaria de bienestar']) ||
                in_array($slugLower, ['administrador', 'secretaria-de-bienestar']);
        });

        $menuPermissions = [];
        foreach ($roles as $rol) {
            $perms = json_decode($rol->menu_permissions, true) ?? [];
            if (is_array($perms)) {
                $menuPermissions = array_merge($menuPermissions, $perms);
            }
        }
        $menuPermissions = array_values(array_unique($menuPermissions));

        if ($esAdmin) {
            $permitidos = DB::table($modulesTable)
                ->where('activo', 1)
                ->pluck('key')
                ->toArray();
        } else {
            $permitidos = DB::table('rol_modulo')
                ->join($modulesTable, $modulesTable . '.id', '=', 'rol_modulo.modulo_id')
                ->whereIn('rol_modulo.rol_id', $roleIds)
                ->where($modulesTable . '.activo', 1)
                ->distinct()
                ->pluck($modulesTable . '.key')
                ->toArray();
        }

        session([
            'modulos_permitidos'    => $permitidos,
            'menu_permissions_user' => $menuPermissions,
            'es_admin'              => $esAdmin,
            'permisos_usuario_id'   => $userId,
            'permisos_version'      => self::SESSION_VERSION,
        ]);
    }
}
