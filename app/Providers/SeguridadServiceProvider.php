<?php

namespace App\Providers;

use App\Support\Auditoria\AuditorModelos;
use Illuminate\Support\ServiceProvider;

class SeguridadServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        AuditorModelos::escuchar();
    }
}
