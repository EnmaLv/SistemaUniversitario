<?php

namespace App\Providers;

use App\Support\Auditoria\AuditorModelos;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Support\ServiceProvider;

class SeguridadServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        AuditorModelos::escuchar();

        $proxies = trim((string) config('seguridad.proxies_confiables', '*'));
        if ($proxies !== '*') {
            TrustProxies::at($proxies === '' ? [] : array_map('trim', explode(',', $proxies)));
        }
    }
}
