<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BecaBeneficioController;
use App\Http\Controllers\BecaController;
use App\Models\Becas\Beneficio;
use App\Models\Becas\Beca;
use App\Http\Resources\BeneficioResource;
use App\Http\Resources\BecaResource;

    Route::prefix('/becas')->group(function () {
        // Rutas de beca
        Route::get('/beneficios', function () {
            return BeneficioResource::collection(Beneficio::paginate(10));
        });

        // Rutas de jornada
        Route::prefix('/jornada')->group(function () {
            Route::get('/', [\App\Http\Controllers\beca\JornadaBecaController::class, 'index'])->name('admin.becas.jornada.index');
            Route::get('/create', [\App\Http\Controllers\beca\JornadaBecaController::class, 'create'])->name('admin.becas.jornada.create');
            Route::post('/store', [\App\Http\Controllers\beca\JornadaBecaController::class, 'store'])->name('admin.becas.jornada.store');
            Route::get('/{jornada}/edit', [\App\Http\Controllers\beca\JornadaBecaController::class, 'edit'])->name('admin.becas.jornada.edit');
            Route::put('/{jornada}', [\App\Http\Controllers\beca\JornadaBecaController::class, 'update'])->name('admin.becas.jornada.update');
            Route::delete('/{jornada}', [\App\Http\Controllers\beca\JornadaBecaController::class, 'destroy'])->name('admin.becas.jornada.destroy');
            Route::put('/{jornada}/activar', [\App\Http\Controllers\beca\JornadaBecaController::class, 'activar'])->name('admin.becas.jornada.activar');
        });

        // Rutas de solicitudes de beca
        Route::prefix('/solicitudes')->group(function () {
            Route::get('/', [\App\Http\Controllers\beca\SolicitudBecaController::class, 'index'])->name('admin.becas.solicitudes.index');
            Route::get('/create', [\App\Http\Controllers\beca\SolicitudBecaController::class, 'create'])->name('admin.becas.solicitudes.create');
            Route::post('/store', [\App\Http\Controllers\beca\SolicitudBecaController::class, 'store'])->name('admin.becas.solicitudes.store');
            Route::get('/{id}', [\App\Http\Controllers\beca\SolicitudBecaController::class, 'show'])->name('admin.becas.solicitudes.show');
            Route::put('/{id}/verificar', [\App\Http\Controllers\beca\SolicitudBecaController::class, 'verificar'])->name('admin.becas.solicitudes.verificar');
        });
    });

    Route::get('becas', function () {
        return BecaResource::collection(Beca::with(['beneficios', 'asignacionesTrabajo.tutor'])->paginate(10));
    });

Route::middleware(['auth', 'tasa.actualizada'])
    ->prefix('/admin')
    ->middleware(\App\Http\Middleware\CheckMenuPermission::class)
    ->group(function () {
        Route::get('/becas', [BecaController::class, 'index'])->name('admin.becas.index');
        Route::get('/becas/create', [BecaController::class, 'create'])->name('admin.becas.create');
        Route::post('/becas/store', [BecaController::class, 'store'])->name('admin.becas.store');

        Route::get('/becas/beneficios', [BecaBeneficioController::class, 'index'])->name('admin.becas.beneficios.index');
        Route::get('/becas/beneficios/create', [BecaBeneficioController::class, 'create'])->name('admin.becas.beneficios.create');
        Route::post('/becas/beneficios/store', [BecaBeneficioController::class, 'store'])->name('admin.becas.beneficios.store');
        Route::get('/becas/beneficios/{beneficio}/edit', [BecaBeneficioController::class, 'edit'])->name('admin.becas.beneficios.edit');
        Route::put('/becas/beneficios/{beneficio}', [BecaBeneficioController::class, 'update'])->name('admin.becas.beneficios.update');
        Route::put('/becas/beneficios/{beneficio}/toggle', [BecaBeneficioController::class, 'toggle'])->name('admin.becas.beneficios.toggle');

        Route::get('/becas/{beca}', [BecaController::class, 'show'])->name('admin.becas.show');
        Route::get('/becas/{beca}/edit', [BecaController::class, 'edit'])->name('admin.becas.edit');
        Route::put('/becas/{beca}', [BecaController::class, 'update'])->name('admin.becas.update');
        Route::put('/becas/{beca}/toggle', [BecaController::class, 'toggle'])->name('admin.becas.toggle');
        Route::get('/becas/{beca}/json', [BecaController::class, 'json'])->name('admin.becas.json');
        
        // Beneficiarios de beca
        Route::post('/becas/{beca}/beneficiarios', [\App\Http\Controllers\BecaBeneficiarioController::class, 'store'])->name('admin.becas.beneficiarios.store');
        Route::put('/becas/{beca}/beneficiarios/{beneficiario}', [\App\Http\Controllers\BecaBeneficiarioController::class, 'update'])->name('admin.becas.beneficiarios.update');
        Route::delete('/becas/{beca}/beneficiarios/{beneficiario}', [\App\Http\Controllers\BecaBeneficiarioController::class, 'destroy'])->name('admin.becas.beneficiarios.destroy');
    });
