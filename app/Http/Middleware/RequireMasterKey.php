<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequireMasterKey
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $isAdmin = false;
            if (method_exists($user, 'roles')) {
                try {
                    $isAdmin = $user->roles()->where('nombre', 'Administrador')->exists();
                } catch (\Exception $e) {
                    $isAdmin = false;
                }
            } else {
                $isAdmin = ($user->role ?? '') === 'Administrador';
            }

            if ($isAdmin) {
            $allowed = [
                'admin/configuracion/master-key',
                'admin/configuracion/master-key/verify',
            ];

                if (!session('master_key_validated') && !in_array($request->path(), $allowed)) {
                    if ($request->isMethod('GET') && ! $request->expectsJson()) {
                        session()->put('url.intended', $request->fullUrl());
                    }
                    return redirect()->route('admin.configuracion.master_key.form');
                }
            }
        }

        return $next($request);
    }
}
