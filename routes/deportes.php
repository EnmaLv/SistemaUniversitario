<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Deporte\HorarioDeporteController;

// Maestros - Horarios Deportes
Route::get('/deportes/maestros/horarios', [HorarioDeporteController::class, 'index'])->name('admin.deportes.maestros.horarios.index');
Route::get('/deportes/maestros/horarios/historial', [HorarioDeporteController::class, 'historial'])->name('admin.deportes.maestros.horarios.historial');
Route::get('/deportes/maestros/horarios/create', [HorarioDeporteController::class, 'create'])->name('admin.deportes.maestros.horarios.create');
Route::post('/deportes/maestros/horarios', [HorarioDeporteController::class, 'store'])->name('admin.deportes.maestros.horarios.store');
Route::get('/deportes/maestros/horarios/{id}/edit', [HorarioDeporteController::class, 'edit'])->name('admin.deportes.maestros.horarios.edit');
Route::put('/deportes/maestros/horarios/{id}', [HorarioDeporteController::class, 'update'])->name('admin.deportes.maestros.horarios.update');
Route::delete('/deportes/maestros/horarios/{id}', [HorarioDeporteController::class, 'destroy'])->name('admin.deportes.maestros.horarios.destroy');
Route::patch('/deportes/maestros/horarios/{id}/activate', [HorarioDeporteController::class, 'activate'])->name('admin.deportes.maestros.horarios.activate');
Route::patch('/deportes/maestros/horarios/{id}/deactivate', [HorarioDeporteController::class, 'deactivate'])->name('admin.deportes.maestros.horarios.deactivate');
Route::get('/deportes/maestros/horarios/exportar-pdf', [HorarioDeporteController::class, 'exportarPdf'])->name('admin.deportes.maestros.horarios.exportarPdf');
Route::get('/deportes/maestros/horarios/{id}', [HorarioDeporteController::class, 'show'])->name('admin.deportes.maestros.horarios.show');
