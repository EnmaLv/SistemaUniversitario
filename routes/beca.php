<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BecaBeneficioController;

Route::prefix('/becas')->group(function () {
    // Rutas de beca
    Route::prefix('/beneficios')->group(function () {
        Route::get('/', [BecaBeneficioController::class, 'index'])->name('admin.becas.beneficios.index');
        Route::get('/create', [BecaBeneficioController::class, 'create'])->name('admin.becas.beneficios.create');
        Route::post('/store', [BecaBeneficioController::class, 'store'])->name('admin.becas.beneficios.store');
        Route::get('/{beneficio}/edit', [BecaBeneficioController::class, 'edit'])->name('admin.becas.beneficios.edit');
        Route::put('/{beneficio}', [BecaBeneficioController::class, 'update'])->name('admin.becas.beneficios.update');
        Route::put('/{beneficio}/toggle', [BecaBeneficioController::class, 'toggle'])->name('admin.becas.beneficios.toggle');
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

    // Rutas exclusivas para el becario/estudiante
    Route::get('/solicitar', [\App\Http\Controllers\beca\SolicitudBecaController::class, 'solicitarEstudiante'])->name('admin.becas.solicitar');

    // Rutas de solicitudes de beca
    Route::prefix('/solicitudes')->group(function () {
        Route::get('/', [\App\Http\Controllers\beca\SolicitudBecaController::class, 'index'])->name('admin.becas.solicitudes.index');
        Route::get('/create', [\App\Http\Controllers\beca\SolicitudBecaController::class, 'create'])->name('admin.becas.solicitudes.create');
        Route::post('/store', [\App\Http\Controllers\beca\SolicitudBecaController::class, 'store'])->name('admin.becas.solicitudes.store');
        Route::get('/{id}', [\App\Http\Controllers\beca\SolicitudBecaController::class, 'show'])->name('admin.becas.solicitudes.show');
        Route::put('/{id}/verificar', [\App\Http\Controllers\beca\SolicitudBecaController::class, 'verificar'])->name('admin.becas.solicitudes.verificar');
    });

    Route::prefix('/preguntas')->group(function () {
        Route::get('/', [\App\Http\Controllers\beca\BecaPreguntaController::class, 'index'])->name('admin.becas.preguntas.index');
        Route::get('/create', [\App\Http\Controllers\beca\BecaPreguntaController::class, 'create'])->name('admin.becas.preguntas.create');
        Route::post('/store', [\App\Http\Controllers\beca\BecaPreguntaController::class, 'store'])->name('admin.becas.preguntas.store');
        Route::get('/{id}/edit', [\App\Http\Controllers\beca\BecaPreguntaController::class, 'edit'])->name('admin.becas.preguntas.edit');
        Route::put('/{id}', [\App\Http\Controllers\beca\BecaPreguntaController::class, 'update'])->name('admin.becas.preguntas.update');
    });

    Route::prefix('/criterios')->group(function () {
        Route::get('/', [\App\Http\Controllers\beca\BeneficioCriterioController::class, 'index'])->name('admin.becas.criterios.index');
        Route::get('/create', [\App\Http\Controllers\beca\BeneficioCriterioController::class, 'create'])->name('admin.becas.criterios.create');
        Route::post('/store', [\App\Http\Controllers\beca\BeneficioCriterioController::class, 'store'])->name('admin.becas.criterios.store');
        Route::get('/{id}/edit', [\App\Http\Controllers\beca\BeneficioCriterioController::class, 'edit'])->name('admin.becas.criterios.edit');
        Route::put('/{id}', [\App\Http\Controllers\beca\BeneficioCriterioController::class, 'update'])->name('admin.becas.criterios.update');
    });
});
