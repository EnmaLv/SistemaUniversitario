<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Configuration\Exceptions;
use App\Http\Middleware\CheckTasaActualizada;
use App\Http\Middleware\IgnorarTasaHoy;
use App\Http\Middleware\RegistrarActividad;
use App\Support\Seguridad\AccesosDenegados;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->append(IgnorarTasaHoy::class);

        $middleware->web(append: [
            CheckTasaActualizada::class,
            RegistrarActividad::class,
        ]);

        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'tasa.actualizada' => CheckTasaActualizada::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            return AccesosDenegados::responder($e, $request);
        });
    })
    ->create();