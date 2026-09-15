<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BusMarcaController;
use App\Http\Controllers\BusModeloController;
use App\Http\Controllers\BusTipoCombustibleController;
use App\Http\Controllers\BusVehiculoController;
use App\Http\Controllers\BusParadaController;
use App\Http\Controllers\BusMantenimientoController;
use App\Http\Controllers\BusViajeController;
use App\Http\Controllers\BusCargaCombustibleController;
use App\Http\Controllers\BusRutaController;

/* Marcas */

Route::get('/transporte/maestros/bus_marcas', [BusMarcaController::class, 'index'])->name('admin.transporte.maestros.bus_marcas.index');
Route::post('/transporte/maestros/bus_marcas/store', [BusMarcaController::class, 'store'])->name('admin.transporte.maestros.bus_marcas.store');
Route::put('/transporte/maestros/bus_marcas/{busMarca}', [BusMarcaController::class, 'update'])->name('admin.transporte.maestros.bus_marcas.update');
Route::delete('/transporte/maestros/bus_marcas/{busMarca}', [BusMarcaController::class, 'destroy'])->name('admin.transporte.maestros.bus_marcas.destroy');
Route::put('/transporte/maestros/bus_marcas/{busMarca}/activar', [BusMarcaController::class, 'activar'])->name('admin.transporte.maestros.bus_marcas.activar');

/* Modelos */
Route::get('/transporte/maestros/bus_modelos', [BusModeloController::class, 'index'])->name('admin.transporte.maestros.bus_modelos.index');
Route::post('/transporte/maestros/bus_modelos/store', [BusModeloController::class, 'store'])->name('admin.transporte.maestros.bus_modelos.store');
Route::put('/transporte/maestros/bus_modelos/{busModelo}', [BusModeloController::class, 'update'])->name('admin.transporte.maestros.bus_modelos.update');
Route::delete('/transporte/maestros/bus_modelos/{busModelo}', [BusModeloController::class, 'destroy'])->name('admin.transporte.maestros.bus_modelos.destroy');
Route::put('/transporte/maestros/bus_modelos/{busModelo}/activar', [BusModeloController::class, 'activar'])->name('admin.transporte.maestros.bus_modelos.activar');

/* Tipo de Combustible */
Route::get('/transporte/maestros/bus_tipo_combustibles', [BusTipoCombustibleController::class, 'index'])->name('admin.transporte.maestros.bus_tipo_combustibles.index');
Route::post('/transporte/maestros/bus_tipo_combustibles/store', [BusTipoCombustibleController::class, 'store'])->name('admin.transporte.maestros.bus_tipo_combustibles.store');
Route::put('/transporte/maestros/bus_tipo_combustibles/{busTipoCombustible}', [BusTipoCombustibleController::class, 'update'])->name('admin.transporte.maestros.bus_tipo_combustibles.update');
Route::delete('/transporte/maestros/bus_tipo_combustibles/{busTipoCombustible}', [BusTipoCombustibleController::class, 'destroy'])->name('admin.transporte.maestros.bus_tipo_combustibles.destroy');
Route::put('/transporte/maestros/bus_tipo_combustibles/{busTipoCombustible}/activar', [BusTipoCombustibleController::class, 'activar'])->name('admin.transporte.maestros.bus_tipo_combustibles.activar');

/* Vehículos */
Route::get('/transporte/maestros/bus_vehiculos', [BusVehiculoController::class, 'index'])->name('admin.transporte.maestros.bus_vehiculos.index');
Route::get('/transporte/maestros/bus_vehiculos/create', [BusVehiculoController::class, 'create'])->name('admin.transporte.maestros.bus_vehiculos.create');
Route::post('/transporte/maestros/bus_vehiculos/store', [BusVehiculoController::class, 'store'])->name('admin.transporte.maestros.bus_vehiculos.store');
Route::get('/transporte/maestros/bus_vehiculos/{busVehiculo}/edit', [BusVehiculoController::class, 'edit'])->name('admin.transporte.maestros.bus_vehiculos.edit');
Route::get('/transporte/maestros/bus_vehiculos/verificar-placa', [BusVehiculoController::class, 'verificarPlaca'])->name('admin.transporte.maestros.bus_vehiculos.verificar_placa');
Route::put('/transporte/maestros/bus_vehiculos/{busVehiculo}', [BusVehiculoController::class, 'update'])->name('admin.transporte.maestros.bus_vehiculos.update');
Route::delete('/transporte/maestros/bus_vehiculos/{busVehiculo}', [BusVehiculoController::class, 'destroy'])->name('admin.transporte.maestros.bus_vehiculos.destroy');
Route::put('/transporte/maestros/bus_vehiculos/{busVehiculo}/activar', [BusVehiculoController::class, 'activar'])->name('admin.transporte.maestros.bus_vehiculos.activar');

/* Rutas */
Route::get('/transporte/maestros/bus_rutas', [BusRutaController::class, 'index'])->name('admin.transporte.maestros.bus_rutas.index');
Route::get('/transporte/maestros/bus_rutas/create', [BusRutaController::class, 'create'])->name('admin.transporte.maestros.bus_rutas.create');
Route::post('/transporte/maestros/bus_rutas/store', [BusRutaController::class, 'store'])->name('admin.transporte.maestros.bus_rutas.store');
Route::get('/transporte/maestros/bus_rutas/{busRuta}/edit', [BusRutaController::class, 'edit'])->name('admin.transporte.maestros.bus_rutas.edit');
Route::get('/transporte/maestros/bus_rutas/verificar-nombre', [BusRutaController::class, 'verificarNombre'])->name('admin.transporte.maestros.bus_rutas.verificar_nombre');
Route::put('/transporte/maestros/bus_rutas/{busRuta}', [BusRutaController::class, 'update'])->name('admin.transporte.maestros.bus_rutas.update');
Route::delete('/transporte/maestros/bus_rutas/{busRuta}', [BusRutaController::class, 'destroy'])->name('admin.transporte.maestros.bus_rutas.destroy');
Route::put('/transporte/maestros/bus_rutas/{busRuta}/activar', [BusRutaController::class, 'activar'])->name('admin.transporte.maestros.bus_rutas.activar');

/* Paradas */
Route::get('/transporte/maestros/bus_paradas/verificar-nombre', [BusParadaController::class, 'verificarNombre'])->name('admin.transporte.maestros.bus_paradas.verificar_nombre');
Route::get('/transporte/maestros/bus_paradas', [BusParadaController::class, 'index'])->name('admin.transporte.maestros.bus_paradas.index');
Route::get('/transporte/maestros/bus_paradas/create', [BusParadaController::class, 'create'])->name('admin.transporte.maestros.bus_paradas.create');
Route::post('/transporte/maestros/bus_paradas/store', [BusParadaController::class, 'store'])->name('admin.transporte.maestros.bus_paradas.store');
Route::get('/transporte/maestros/bus_paradas/{busParada}/edit', [BusParadaController::class, 'edit'])->name('admin.transporte.maestros.bus_paradas.edit');
Route::put('/transporte/maestros/bus_paradas/{busParada}', [BusParadaController::class, 'update'])->name('admin.transporte.maestros.bus_paradas.update');
Route::delete('/transporte/maestros/bus_paradas/{busParada}', [BusParadaController::class, 'destroy'])->name('admin.transporte.maestros.bus_paradas.destroy');
Route::put('/transporte/maestros/bus_paradas/{busParada}/activar', [BusParadaController::class, 'activar'])->name('admin.transporte.maestros.bus_paradas.activar');

/* Mantenimiento */
Route::get('/transporte/maestros/bus_mantenimientos', [BusMantenimientoController::class, 'index'])->name('admin.transporte.maestros.bus_mantenimientos.index');
Route::get('/transporte/maestros/bus_mantenimientos/create', [BusMantenimientoController::class, 'create'])->name('admin.transporte.maestros.bus_mantenimientos.create');
Route::post('/transporte/maestros/bus_mantenimientos/store', [BusMantenimientoController::class, 'store'])->name('admin.transporte.maestros.bus_mantenimientos.store');
Route::get('/transporte/maestros/bus_mantenimientos/{busMantenimiento}/edit', [BusMantenimientoController::class, 'edit'])->name('admin.transporte.maestros.bus_mantenimientos.edit');
Route::put('/transporte/maestros/bus_mantenimientos/{busMantenimiento}', [BusMantenimientoController::class, 'update'])->name('admin.transporte.maestros.bus_mantenimientos.update');
Route::delete('/transporte/maestros/bus_mantenimientos/{busMantenimiento}', [BusMantenimientoController::class, 'destroy'])->name('admin.transporte.maestros.bus_mantenimientos.destroy');

/* Viajes */
Route::get('bus-viajes/{busViaje}/gps-logs', [BusViajeController::class, 'gpsLogs'])->name('admin.transporte.maestros.bus_viajes.gps-logs');
Route::get('/transporte/maestros/bus_viajes', [BusViajeController::class, 'index'])->name('admin.transporte.maestros.bus_viajes.index');
Route::get('/transporte/maestros/bus_viajes/create', [BusViajeController::class, 'create'])->name('admin.transporte.maestros.bus_viajes.create');
Route::post('/transporte/maestros/bus_viajes/store', [BusViajeController::class, 'store'])->name('admin.transporte.maestros.bus_viajes.store');
Route::get('/transporte/maestros/bus_viajes/{busViaje}/edit', [BusViajeController::class, 'edit'])->name('admin.transporte.maestros.bus_viajes.edit');
Route::get('/transporte/maestros/bus_viajes/{busViaje}', [BusViajeController::class, 'show'])->name('admin.transporte.maestros.bus_viajes.show');
Route::put('/transporte/maestros/bus_viajes/{busViaje}', [BusViajeController::class, 'update'])->name('admin.transporte.maestros.bus_viajes.update');
Route::delete('/transporte/maestros/bus_viajes/{busViaje}', [BusViajeController::class, 'destroy'])->name('admin.transporte.maestros.bus_viajes.destroy');
Route::post('/transporte/maestros/bus_viajes/{busViaje}/cancelar', [BusViajeController::class, 'cancelar'])->name('admin.transporte.maestros.bus_viajes.cancelar');

/* Cargas de Combustible */
Route::get('/transporte/maestros/bus_carga_combustibles', [BusCargaCombustibleController::class, 'index'])->name('admin.transporte.maestros.bus_carga_combustibles.index');
Route::get('/transporte/maestros/bus_carga_combustibles/create', [BusCargaCombustibleController::class, 'create'])->name('admin.transporte.maestros.bus_carga_combustibles.create');
Route::post('/transporte/maestros/bus_carga_combustibles/store', [BusCargaCombustibleController::class, 'store'])->name('admin.transporte.maestros.bus_carga_combustibles.store');
Route::get('/transporte/maestros/bus_carga_combustibles/{busCargaCombustible}/edit', [BusCargaCombustibleController::class, 'edit'])->name('admin.transporte.maestros.bus_carga_combustibles.edit');
Route::put('/transporte/maestros/bus_carga_combustibles/{busCargaCombustible}', [BusCargaCombustibleController::class, 'update'])->name('admin.transporte.maestros.bus_carga_combustibles.update');
Route::delete('/transporte/maestros/bus_carga_combustibles/{busCargaCombustible}', [BusCargaCombustibleController::class, 'destroy'])->name('admin.transporte.maestros.bus_carga_combustibles.destroy');
