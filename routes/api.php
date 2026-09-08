<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\BusMarcaApiController;
use App\Http\Controllers\Api\BusModeloApiController;
use App\Http\Controllers\Api\BusTipoCombustibleApiController;
use App\Http\Controllers\Api\BusVehiculoApiController;
use App\Http\Controllers\Api\BusViajeApiController;
use App\Http\Controllers\Api\BusParadaApiController;

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
        Route::get('marcas',               [BusMarcaApiController::class, 'index']);
        Route::post('marcas',               [BusMarcaApiController::class, 'store']);
        Route::get('marcas/{marca}',       [BusMarcaApiController::class, 'show']);
        Route::put('marcas/{marca}',       [BusMarcaApiController::class, 'update']);
        Route::patch('marcas/{marca}/toggle', [BusMarcaApiController::class, 'toggle']);
        Route::delete('marcas/{marca}',       [BusMarcaApiController::class, 'destroy']);

        Route::get('modelos',               [BusModeloApiController::class, 'index']);
        Route::post('modelos',               [BusModeloApiController::class, 'store']);
        Route::get('modelos/{modelo}',      [BusModeloApiController::class, 'show']);
        Route::put('modelos/{modelo}',      [BusModeloApiController::class, 'update']);
        Route::patch('modelos/{modelo}/toggle', [BusModeloApiController::class, 'toggle']);
        Route::delete('modelos/{modelo}',      [BusModeloApiController::class, 'destroy']);

        Route::get('combustibles',                  [BusTipoCombustibleApiController::class, 'index']);
        Route::post('combustibles',                 [BusTipoCombustibleApiController::class, 'store']);
        Route::get('combustibles/{combustible}',    [BusTipoCombustibleApiController::class, 'show']);
        Route::put('combustibles/{combustible}',    [BusTipoCombustibleApiController::class, 'update']);
        Route::patch('combustibles/{combustible}/toggle', [BusTipoCombustibleApiController::class, 'toggle']);
        Route::delete('combustibles/{combustible}', [BusTipoCombustibleApiController::class, 'destroy']);

        Route::get('vehiculos',               [BusVehiculoApiController::class, 'index']);
        Route::post('vehiculos',              [BusVehiculoApiController::class, 'store']);
        Route::get('vehiculos/{vehiculo}',    [BusVehiculoApiController::class, 'show']);
        Route::put('vehiculos/{vehiculo}',    [BusVehiculoApiController::class, 'update']);
        Route::patch('vehiculos/{vehiculo}/toggle', [BusVehiculoApiController::class, 'toggle']);
        Route::delete('vehiculos/{vehiculo}', [BusVehiculoApiController::class, 'destroy']);

        Route::get('paradas', [BusParadaApiController::class, 'index']);

        Route::get('viajes/hoy', [BusViajeApiController::class, 'cartelera']);
        Route::get('mi-viaje-activo', [BusViajeApiController::class, 'miViajeActivo']);
        Route::post('viajes/{viaje}/iniciar', [BusViajeApiController::class, 'iniciar']);
        Route::post('viajes/{viaje}/finalizar', [BusViajeApiController::class, 'finalizar']);
        Route::post('viajes/{viaje}/cancelar', [BusViajeApiController::class, 'cancelar']);
        Route::get('viajes/historial', [BusViajeApiController::class, 'historial']);
        Route::post('viajes/{viaje}/gps',       [BusViajeApiController::class, 'registrarGps']);
    });
});
