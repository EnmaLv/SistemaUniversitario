<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RedirectIfAuthenticated::redirectUsing(function ($request) {
            if (Auth::check()) {
                $user = Auth::user();
                return (new \App\AdminLTE\Filters\ModuleFilter())
                    ->resolveInitialRoute($user->id_usuario ?? $user->id);
            }
            return '/';
        });
        View::composer(['admin.psicologia.*', 'admin.enfermedades.*'], function ($view) {
            $data = $view->getData();
            $categoriaVal = $data['categoria'] ?? $data['categoriaFiltro'] ?? request('categoria') ?? request('categoriaFiltro') ?? '';

            $tipoVal = $data['tipo'] ?? request('tipo') ?? session('modulo_activo', 'general');

            $esPsicologia = in_array(strtolower($categoriaVal), ['salud', 'psicologia', 'psicología'])
                || in_array(strtolower($tipoVal), ['salud', 'psicologia', 'psicología']);

            $themeColor = $esPsicologia ? 'indigo' : 'blue';

            $categoriaTexto = match ($categoriaVal) {
                'salud' => 'Salud Mental / Psiquiátrica',
                'biopsicosocial' => 'Biopsicosocial',
                default => 'Salud General / Médica',
            };

            $view->with([
                'esPsicologia' => $esPsicologia,
                'themeColor' => $themeColor,
                'categoriaTexto' => $categoriaTexto,
                'btnClass' => $esPsicologia
                    ? 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-500/20'
                    : 'bg-blue-600 hover:bg-blue-700 shadow-blue-500/20',
                'focusRingClass' => $esPsicologia
                    ? 'focus:ring-indigo-500/30 focus:border-indigo-500'
                    : 'focus:ring-blue-500/30 focus:border-blue-500',
                'categoriaBadgeClass' => $esPsicologia
                    ? 'bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800'
                    : 'bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-800',
                'spinnerColor' => $esPsicologia ? 'border-indigo-600' : 'border-blue-600',
            ]);
        });

        Blade::if('canMenu', function ($keys) {
            return auth()->check() && auth()->user()->canAccessMenu($keys);
        });

        Blade::if('canModule', function ($moduleKey) {
            if (!auth()->check()) {
                return false;
            }

            $moduloActivo = session('modulo_activo');
            $permitidos = session('modulos_permitidos', []);

            if (!$moduloActivo || $moduloActivo !== $moduleKey || !in_array($moduleKey, $permitidos)) {
                return false;
            }

            return true;
        });

        $mainPath = database_path('migrations');
        $directories = glob($mainPath . '/*', GLOB_ONLYDIR);
        $paths = array_merge([$mainPath], $directories);
        $this->loadMigrationsFrom($paths);

        if (!app()->runningInConsole()) {
            try {
                if (!Schema::hasTable('sessions')) {
                    Artisan::call('migrate', ['--force' => true]);
                    Artisan::call('db:seed', ['--force' => true]);
                }
            } catch (\Exception $e) {
            }
        }
    }
}
