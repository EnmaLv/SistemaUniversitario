<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\ExchangeRates;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckTasaActualizada
{
    public function handle($request, Closure $next)
    {
        // 1. Si no hay usuario autenticado, no hacer nada
        if (!auth()->check()) {
            return $next($request);
        }

        // 2. Rutas exentas
        if (
            $request->routeIs('productos.actualizar.tasa') ||
            $request->routeIs('tasa.ignorar') ||
            $request->routeIs('logout') ||
            $request->routeIs('login') ||
            $request->routeIs('register') ||
            $request->routeIs('password.*')
        ) {
            return $next($request);
        }

        $hoy = Carbon::today()->toDateString();

        $ultimaTasa = ExchangeRates::where('nombre', 'Oficial')
            ->whereNotNull('fecha_vigencia')
            ->orderByDesc('fecha_vigencia')
            ->orderByDesc('id')
            ->first();

        // 3. Sin tasa: marcar flag pero NO redirigir (evita el loop)
        if (!$ultimaTasa) {
            session()->put('tasa_obligatoria', true);
            session()->forget('tasa_pendiente');
            return $next($request);
        }

        // 4. Con tasa: decidir si está pendiente
        $tasaHoy     = $ultimaTasa->fecha_vigencia === $hoy;
        $ignoradaHoy = session('tasa_ignorada_hasta') === $hoy;

        if (!$tasaHoy && !$ignoradaHoy) {
            session()->put('tasa_pendiente', true);
            session()->forget('tasa_obligatoria');
        } else {
            session()->forget(['tasa_pendiente', 'tasa_obligatoria']);
        }

        return $next($request);
    }
}