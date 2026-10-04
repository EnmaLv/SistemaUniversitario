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

        // Admin / Secretaria → acceso total
        foreach ($user->roles ?? [] as $r) {
            $nombreRol = mb_strtolower($r->nombre ?? '');
            $slugRol   = mb_strtolower($r->slug ?? '');
            if (
                in_array($nombreRol, ['administrador', 'secretaria de bienestar'], true)
                || in_array($slugRol, ['administrador', 'secretaria-de-bienestar'], true)
            ) {
                return $next($request);
            }
        }

        // Rutas siempre permitidas
        $allowedPaths = [
            'admin/configuracion/master-key',
            'admin/configuracion/master-key/verify',
        ];
        $allowedRouteNames = [
            'admin.configuracion.master_key.form',
            'admin.configuracion.master_key.verify',
            'admin.becas.solicitudes.store',
            'admin.becas.solicitudes.renovar',
            'admin.becas.lapsos.avanzar',
        ];

        $routeName   = $request->route() ? $request->route()->getName() : null;
        $currentPath = ltrim($request->path(), '/');

        if (in_array($currentPath, $allowedPaths) || ($routeName && in_array($routeName, $allowedRouteNames))) {
            return $next($request);
        }

        // ─── Permisos efectivos ───
        $rolePermissions = collect($user->roles)
            ->pluck('menu_permissions')
            ->flatten()
            ->filter()
            ->unique()
            ->values()
            ->all();

        $extra     = is_array($user->extra_permissions ?? null)
            ? $user->extra_permissions
            : (is_string($user->extra_permissions ?? null) ? json_decode($user->extra_permissions, true) : []);
        $userAllow = $extra['allow'] ?? [];
        $userDeny  = $extra['deny'] ?? [];

        // ─── Mapa key → rutas (config nuevo) ───
        $keyToPatterns = config('menu_routes', []);

        // Expande keys a patrones de ruta.
        // Si una key no está mapeada, se usa tal cual como patrón
        // (útil para keys que literalmente son nombres de ruta).
        $expand = function (array $arr) use ($keyToPatterns): array {
            $out = [];
            foreach ($arr as $p) {
                if (isset($keyToPatterns[$p])) {
                    foreach ($keyToPatterns[$p] as $pat) {
                        $out[] = $pat;
                    }
                } else {
                    $out[] = $p;
                }
            }
            return array_values(array_unique($out));
        };

        $rolePatterns = $expand($rolePermissions);
        $userAllow    = $expand($userAllow);
        $userDeny     = $expand($userDeny);

        // Deny gana siempre
        foreach ($userDeny as $p) {
            if (Str::is($p, $currentPath) || ($routeName && Str::is($p, $routeName))) {
                abort(403);
            }
        }

        // Allow específico del usuario
        foreach ($userAllow as $p) {
            if (Str::is($p, $currentPath) || ($routeName && Str::is($p, $routeName))) {
                return $next($request);
            }
        }

        // Permisos heredados del rol
        foreach ($rolePatterns as $p) {
            if (Str::is($p, $currentPath) || ($routeName && Str::is($p, $routeName))) {
                return $next($request);
            }
        }

        abort(403);
    }
}