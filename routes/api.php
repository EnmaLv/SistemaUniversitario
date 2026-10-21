<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\BusMarcaApiController;
use App\Http\Controllers\Api\BusModeloApiController;
use App\Http\Controllers\Api\BusTipoCombustibleApiController;
use App\Http\Controllers\Api\BusVehiculoApiController;
use App\Http\Controllers\Api\BusViajeApiController;
use App\Http\Controllers\Api\BusParadaApiController;
use App\Http\Controllers\Api\BusViajePasajeroApiController;
use App\Http\Controllers\SedeController;

Route::get('/ping', fn() => response()->json(['ok' => true]));

Route::prefix('auth')->group(function () {
    Route::post('/login',  [AuthApiController::class, 'login']);
    Route::post('/logout', [AuthApiController::class, 'logout'])->middleware('auth:sanctum');
});

Route::get('viajes/{viaje}/posicion', [BusViajeApiController::class, 'obtenerPosicion']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me',      [AuthApiController::class, 'me']);
    Route::get('/modulos', [AuthApiController::class, 'modulos']);

    Route::prefix('/')->group(function () {
        Route::get('marcas',               [BusMarcaApiController::class, 'index'])->middleware('permiso.api:bus_marcas');
        Route::post('marcas',               [BusMarcaApiController::class, 'store'])->middleware('permiso.api:bus_marcas');
        Route::get('marcas/{marca}',       [BusMarcaApiController::class, 'show'])->middleware('permiso.api:bus_marcas');
        Route::put('marcas/{marca}',       [BusMarcaApiController::class, 'update'])->middleware('permiso.api:bus_marcas');
        Route::patch('marcas/{marca}/toggle', [BusMarcaApiController::class, 'toggle'])->middleware('permiso.api:bus_marcas');
        Route::delete('marcas/{marca}',       [BusMarcaApiController::class, 'destroy'])->middleware('permiso.api:bus_marcas');

        Route::get('modelos',               [BusModeloApiController::class, 'index'])->middleware('permiso.api:bus_modelos');
        Route::post('modelos',               [BusModeloApiController::class, 'store'])->middleware('permiso.api:bus_modelos');
        Route::get('modelos/{modelo}',      [BusModeloApiController::class, 'show'])->middleware('permiso.api:bus_modelos');
        Route::put('modelos/{modelo}',      [BusModeloApiController::class, 'update'])->middleware('permiso.api:bus_modelos');
        Route::patch('modelos/{modelo}/toggle', [BusModeloApiController::class, 'toggle'])->middleware('permiso.api:bus_modelos');
        Route::delete('modelos/{modelo}',      [BusModeloApiController::class, 'destroy'])->middleware('permiso.api:bus_modelos');

        Route::get('combustibles',                  [BusTipoCombustibleApiController::class, 'index'])->middleware('permiso.api:bus_tipo_combustibles');
        Route::post('combustibles',                 [BusTipoCombustibleApiController::class, 'store'])->middleware('permiso.api:bus_tipo_combustibles');
        Route::get('combustibles/{combustible}',    [BusTipoCombustibleApiController::class, 'show'])->middleware('permiso.api:bus_tipo_combustibles');
        Route::put('combustibles/{combustible}',    [BusTipoCombustibleApiController::class, 'update'])->middleware('permiso.api:bus_tipo_combustibles');
        Route::patch('combustibles/{combustible}/toggle', [BusTipoCombustibleApiController::class, 'toggle'])->middleware('permiso.api:bus_tipo_combustibles');
        Route::delete('combustibles/{combustible}', [BusTipoCombustibleApiController::class, 'destroy'])->middleware('permiso.api:bus_tipo_combustibles');

        Route::get('vehiculos',               [BusVehiculoApiController::class, 'index'])->middleware('permiso.api:bus_vehiculos');
        Route::post('vehiculos',              [BusVehiculoApiController::class, 'store'])->middleware('permiso.api:bus_vehiculos');
        Route::get('vehiculos/{vehiculo}',    [BusVehiculoApiController::class, 'show'])->middleware('permiso.api:bus_vehiculos');
        Route::put('vehiculos/{vehiculo}',    [BusVehiculoApiController::class, 'update'])->middleware('permiso.api:bus_vehiculos');
        Route::patch('vehiculos/{vehiculo}/toggle', [BusVehiculoApiController::class, 'toggle'])->middleware('permiso.api:bus_vehiculos');
        Route::delete('vehiculos/{vehiculo}', [BusVehiculoApiController::class, 'destroy'])->middleware('permiso.api:bus_vehiculos');

        Route::get('paradas', [BusParadaApiController::class, 'index']);
        Route::get('sedes', [SedeController::class, 'index']);

        Route::post('viajes/{viaje}/pasajeros', [BusViajePasajeroApiController::class, 'registrar']);
        Route::get('viajes/hoy', [BusViajeApiController::class, 'cartelera']);
        Route::get('mi-viaje-activo', [BusViajeApiController::class, 'miViajeActivo']);
        Route::post('viajes/{viaje}/iniciar', [BusViajeApiController::class, 'iniciar']);
        Route::post('viajes/{viaje}/finalizar', [BusViajeApiController::class, 'finalizar']);
        Route::post('viajes/{viaje}/cancelar', [BusViajeApiController::class, 'cancelar']);
        Route::get('viajes/historial', [BusViajeApiController::class, 'historial']);
        Route::post('viajes/{viaje}/gps',       [BusViajeApiController::class, 'registrarGps']);
    });
});
