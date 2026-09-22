<?php

namespace App\Http\Middleware;

use App\Models\ExchangeRates;
use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;

class IgnorarTasaHoy
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->has('ignorar_tasa_hoy')) {
            session()->put(
                'tasa_ignorada_hasta',
                Carbon::today()->toDateString()
            );
        }

        return $next($request);
    }
}
