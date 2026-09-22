<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckMenuPermission
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (! $user) {
            return $next($request);
        }

        foreach ($user->roles ?? [] as $r) {
            if (isset($r->nombre) && in_array(mb_strtolower($r->nombre), ['administrador', 'secretaria de bienestar'])) {
                return $next($request);
            }
        }

        $allowedPaths = [
            'admin/configuracion/master-key',
            'admin/configuracion/master-key/verify',
        ];
        $routeName = $request->route() ? $request->route()->getName() : null;
        $allowedRouteNames = [
            'admin.configuracion.master_key.form',
            'admin.configuracion.master_key.verify',
            'admin.becas.solicitudes.store',
            'admin.becas.solicitudes.renovar',
            'admin.becas.lapsos.avanzar',
        ];

        $currentPath = ltrim($request->path(), '/');
        if (in_array($currentPath, $allowedPaths) || ($routeName && in_array($routeName, $allowedRouteNames))) {
            return $next($request);
        }

        $rolePatterns = collect($user->roles)->pluck('menu_permissions')->flatten()->filter()->unique()->values()->all();

        $menu = config('adminlte.menu', []);
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

        $expandedRolePatterns = [];
        foreach ($rolePatterns as $p) {
            if (isset($keyToPatterns[$p])) {
                foreach ($keyToPatterns[$p] as $pat) {
                    $expandedRolePatterns[] = $pat;
                }
            } else {
                $expandedRolePatterns[] = $p;
            }
        }
        $rolePatterns = array_values(array_unique($expandedRolePatterns));
        $extra = is_array($user->extra_permissions ?? null) ? $user->extra_permissions : (is_string($user->extra_permissions) ? json_decode($user->extra_permissions, true) : []);
        $userAllow = $extra['allow'] ?? [];
        $userDeny = $extra['deny'] ?? [];

        $expandUser = function ($arr) use ($keyToPatterns) {
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

        $userAllow = $expandUser($userAllow);
        $userDeny = $expandUser($userDeny);

        $path = ltrim($request->path(), '/');
        $routeName = $request->route() ? $request->route()->getName() : null;

        foreach ($userDeny as $p) {
            if (Str::is($p, $path) || ($routeName && Str::is($p, $routeName))) {
                abort(403);
            }
        }

        foreach ($userAllow as $p) {
            if (Str::is($p, $path) || ($routeName && Str::is($p, $routeName))) {
                return $next($request);
            }
        }

        if (! empty($rolePatterns)) {
            foreach ($rolePatterns as $p) {
                if (Str::is($p, $path) || ($routeName && Str::is($p, $routeName))) {
                    return $next($request);
                }

                if (Str::is($p . '*', $path) || ($routeName && Str::is($p . '*', $routeName))) {
                    return $next($request);
                }

                if (Str::contains($path, $p) || ($routeName && Str::contains($routeName, $p))) {
                    return $next($request);
                }
            }

            abort(403);
        }

        return $next($request);
    }
}
