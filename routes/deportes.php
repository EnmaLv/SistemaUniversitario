<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Deporte\HorarioDeporteController;

Route::get('/deportes/maestros/horarios', [HorarioDeporteController::class, 'index'])->name('admin.deportes.maestros.horarios.index');
Route::get('/deportes/maestros/horarios/exportar-pdf', [HorarioDeporteController::class, 'exportarPdf'])->name('admin.deportes.maestros.horarios.exportarPdf');

Route::prefix('/deportes')->name('admin.deportes.')->group(function () {
    Route::get('/espacios', [\App\Http\Controllers\Deporte\EspacioDeportivoController::class, 'index'])->name('espacios.index');
    Route::get('/espacios/create', [\App\Http\Controllers\Deporte\EspacioDeportivoController::class, 'create'])->name('espacios.create');
    Route::post('/espacios', [\App\Http\Controllers\Deporte\EspacioDeportivoController::class, 'store'])->name('espacios.store');
    Route::get('/espacios/{espacio}/edit', [\App\Http\Controllers\Deporte\EspacioDeportivoController::class, 'edit'])->whereNumber('espacio')->name('espacios.edit');
    Route::put('/espacios/{espacio}', [\App\Http\Controllers\Deporte\EspacioDeportivoController::class, 'update'])->whereNumber('espacio')->name('espacios.update');
    Route::delete('/espacios/{espacio}', [\App\Http\Controllers\Deporte\EspacioDeportivoController::class, 'destroy'])->whereNumber('espacio')->name('espacios.destroy');
    Route::put('/espacios/{espacio}/activar', [\App\Http\Controllers\Deporte\EspacioDeportivoController::class, 'activar'])->whereNumber('espacio')->name('espacios.activar');

    Route::get('/actividades', [\App\Http\Controllers\Deporte\ActividadDeportivaController::class, 'index'])->name('actividades.index');
    Route::get('/actividades/create', [\App\Http\Controllers\Deporte\ActividadDeportivaController::class, 'create'])->name('actividades.create');
    Route::post('/actividades', [\App\Http\Controllers\Deporte\ActividadDeportivaController::class, 'store'])->name('actividades.store');
    Route::get('/actividades/{actividad}/edit', [\App\Http\Controllers\Deporte\ActividadDeportivaController::class, 'edit'])->whereNumber('actividad')->name('actividades.edit');
    Route::put('/actividades/{actividad}', [\App\Http\Controllers\Deporte\ActividadDeportivaController::class, 'update'])->whereNumber('actividad')->name('actividades.update');
    Route::delete('/actividades/{actividad}', [\App\Http\Controllers\Deporte\ActividadDeportivaController::class, 'destroy'])->whereNumber('actividad')->name('actividades.destroy');
    Route::put('/actividades/{actividad}/activar', [\App\Http\Controllers\Deporte\ActividadDeportivaController::class, 'activar'])->whereNumber('actividad')->name('actividades.activar');

    Route::get('/calendarios', [\App\Http\Controllers\Deporte\CalendarioDeportivoController::class, 'index'])->name('calendarios.index');
    Route::get('/calendarios/create', [\App\Http\Controllers\Deporte\CalendarioDeportivoController::class, 'create'])->name('calendarios.create');
    Route::post('/calendarios', [\App\Http\Controllers\Deporte\CalendarioDeportivoController::class, 'store'])->name('calendarios.store');
    Route::get('/calendarios/{calendario}', [\App\Http\Controllers\Deporte\CalendarioDeportivoController::class, 'show'])->whereNumber('calendario')->name('calendarios.show');
    Route::get('/calendarios/{calendario}/cartel-pdf', [\App\Http\Controllers\Deporte\CalendarioDeportivoController::class, 'cartelPdf'])->whereNumber('calendario')->name('calendarios.cartel_pdf');
    Route::get('/calendarios/{calendario}/edit', [\App\Http\Controllers\Deporte\CalendarioDeportivoController::class, 'edit'])->whereNumber('calendario')->name('calendarios.edit');
    Route::put('/calendarios/{calendario}', [\App\Http\Controllers\Deporte\CalendarioDeportivoController::class, 'update'])->whereNumber('calendario')->name('calendarios.update');
    Route::delete('/calendarios/{calendario}', [\App\Http\Controllers\Deporte\CalendarioDeportivoController::class, 'destroy'])->whereNumber('calendario')->name('calendarios.destroy');
    Route::put('/calendarios/{calendario}/activar', [\App\Http\Controllers\Deporte\CalendarioDeportivoController::class, 'activar'])->whereNumber('calendario')->name('calendarios.activar');

    Route::get('/torneos', [\App\Http\Controllers\Deporte\TorneoDeportivoController::class, 'index'])->name('torneos.index');
    Route::get('/torneos/create', [\App\Http\Controllers\Deporte\TorneoDeportivoController::class, 'create'])->name('torneos.create');
    Route::post('/torneos', [\App\Http\Controllers\Deporte\TorneoDeportivoController::class, 'store'])->name('torneos.store');
    Route::get('/torneos/{torneo}', [\App\Http\Controllers\Deporte\TorneoDeportivoController::class, 'show'])->whereNumber('torneo')->name('torneos.show');
    Route::get('/torneos/{torneo}/edit', [\App\Http\Controllers\Deporte\TorneoDeportivoController::class, 'edit'])->whereNumber('torneo')->name('torneos.edit');
    Route::put('/torneos/{torneo}', [\App\Http\Controllers\Deporte\TorneoDeportivoController::class, 'update'])->whereNumber('torneo')->name('torneos.update');
    Route::patch('/torneos/{torneo}/estado', [\App\Http\Controllers\Deporte\TorneoDeportivoController::class, 'updateEstado'])->whereNumber('torneo')->name('torneos.update_estado');
    Route::get('/torneos/{torneo}/cartel-pdf', [\App\Http\Controllers\Deporte\TorneoDeportivoController::class, 'cartelPdf'])->whereNumber('torneo')->name('torneos.cartel_pdf');
    Route::post('/torneos/{torneo}/equipos', [\App\Http\Controllers\Deporte\TorneoDeportivoController::class, 'storeEquipo'])->whereNumber('torneo')->name('torneos.equipos.store');
    Route::delete('/torneos/equipos/{equipo}', [\App\Http\Controllers\Deporte\TorneoDeportivoController::class, 'destroyEquipo'])->whereNumber('equipo')->name('torneos.equipos.destroy');
    Route::post('/torneos/equipos/{equipo}/integrantes', [\App\Http\Controllers\Deporte\TorneoDeportivoController::class, 'storeIntegrante'])->whereNumber('equipo')->name('torneos.integrantes.store');
    Route::delete('/torneos/integrantes/{integrante}', [\App\Http\Controllers\Deporte\TorneoDeportivoController::class, 'destroyIntegrante'])->whereNumber('integrante')->name('torneos.integrantes.destroy');

    Route::get('/inscripciones', [\App\Http\Controllers\Deporte\InscripcionDeportivaController::class, 'index'])->name('inscripciones.index');
    Route::get('/inscripciones/buscar-persona', [\App\Http\Controllers\Deporte\InscripcionDeportivaController::class, 'buscarPersona'])->name('inscripciones.buscar_persona');
    Route::post('/inscripciones', [\App\Http\Controllers\Deporte\InscripcionDeportivaController::class, 'store'])->name('inscripciones.store');
    Route::delete('/inscripciones/{inscripcion}', [\App\Http\Controllers\Deporte\InscripcionDeportivaController::class, 'destroy'])->whereNumber('inscripcion')->name('inscripciones.destroy');

    Route::get('/asistencia', [\App\Http\Controllers\Deporte\AsistenciaDeportivaController::class, 'index'])->name('asistencia.index');
    Route::post('/asistencia', [\App\Http\Controllers\Deporte\AsistenciaDeportivaController::class, 'store'])->name('asistencia.store');
    Route::get('/asistencia/historial/{calendario}', [\App\Http\Controllers\Deporte\AsistenciaDeportivaController::class, 'historial'])->whereNumber('calendario')->name('asistencia.historial');
    Route::get('/asistencia/historial/{calendario}/pdf', [\App\Http\Controllers\Deporte\AsistenciaDeportivaController::class, 'historialPdf'])->whereNumber('calendario')->name('asistencia.historial_pdf');
    Route::get('/asistencia/{sesion}', [\App\Http\Controllers\Deporte\AsistenciaDeportivaController::class, 'show'])->whereNumber('sesion')->name('asistencia.show');
    Route::post('/asistencia/{sesion}/presentes', [\App\Http\Controllers\Deporte\AsistenciaDeportivaController::class, 'storePresentes'])->whereNumber('sesion')->name('asistencia.store_presentes');
    Route::post('/asistencia/{sesion}/quitar', [\App\Http\Controllers\Deporte\AsistenciaDeportivaController::class, 'storeQuitar'])->whereNumber('sesion')->name('asistencia.store_quitar');
    Route::post('/asistencia/{sesion}/cerrar', [\App\Http\Controllers\Deporte\AsistenciaDeportivaController::class, 'storeCierre'])->whereNumber('sesion')->name('asistencia.store_cierre');
    Route::post('/asistencia/{sesion}/reabrir', [\App\Http\Controllers\Deporte\AsistenciaDeportivaController::class, 'reabrir'])->whereNumber('sesion')->name('asistencia.reabrir');
    Route::get('/asistencia/{sesion}/reporte', [\App\Http\Controllers\Deporte\AsistenciaDeportivaController::class, 'reporte'])->whereNumber('sesion')->name('asistencia.reporte');
    Route::get('/asistencia/{sesion}/reporte-pdf', [\App\Http\Controllers\Deporte\AsistenciaDeportivaController::class, 'reportePdf'])->whereNumber('sesion')->name('asistencia.reporte_pdf');
});

Route::prefix('/deportes')->name('admin.deportes.')->group(function () {
    Route::get('/prestamos', [\App\Http\Controllers\Deporte\PrestamoDeporteController::class, 'index'])->name('prestamos.index');
    Route::get('/prestamos/create', [\App\Http\Controllers\Deporte\PrestamoDeporteController::class, 'create'])->name('prestamos.create');
    Route::post('/prestamos', [\App\Http\Controllers\Deporte\PrestamoDeporteController::class, 'store'])->name('prestamos.store');
    Route::get('/prestamos/{id}', [\App\Http\Controllers\Deporte\PrestamoDeporteController::class, 'show'])->whereNumber('id')->name('prestamos.show');
    Route::put('/prestamos/{id}/devolucion', [\App\Http\Controllers\Deporte\PrestamoDeporteController::class, 'updateDevolucion'])->whereNumber('id')->name('prestamos.update_devolucion');

    Route::get('/sanciones', [\App\Http\Controllers\Deporte\SancionDeporteController::class, 'index'])->name('sanciones.index');
    Route::get('/sanciones/{id}', [\App\Http\Controllers\Deporte\SancionDeporteController::class, 'show'])->whereNumber('id')->name('sanciones.show');
    Route::put('/sanciones/{id}/reposicion', [\App\Http\Controllers\Deporte\SancionDeporteController::class, 'updateReposicion'])->whereNumber('id')->name('sanciones.update_reposicion');

    Route::get('/solvencia', [\App\Http\Controllers\Deporte\SolvenciaDeporteController::class, 'index'])->name('solvencia.index');
    Route::get('/solvencia/pdf', [\App\Http\Controllers\Deporte\SolvenciaDeporteController::class, 'pdf'])->name('solvencia.pdf');

    Route::get('/entregas', [\App\Http\Controllers\Deporte\EntregaMaterialController::class, 'index'])->name('entregas.index');
    Route::get('/entregas/create', [\App\Http\Controllers\Deporte\EntregaMaterialController::class, 'create'])->name('entregas.create');
    Route::post('/entregas', [\App\Http\Controllers\Deporte\EntregaMaterialController::class, 'store'])->name('entregas.store');
    Route::get('/entregas/{id}', [\App\Http\Controllers\Deporte\EntregaMaterialController::class, 'show'])->whereNumber('id')->name('entregas.show');
});

Route::prefix('/deportes/implementos')->name('admin.deportes.implementos.')->controller(\App\Http\Controllers\ImplementoMaterialController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/', 'store')->name('store');
    Route::get('/{producto}/edit', 'edit')->whereNumber('producto')->name('edit');
    Route::put('/{producto}', 'update')->whereNumber('producto')->name('update');
    Route::delete('/{producto}', 'destroy')->whereNumber('producto')->name('destroy');
    Route::put('/{producto}/activar', 'activar')->whereNumber('producto')->name('activar');
});
