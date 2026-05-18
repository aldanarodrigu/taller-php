<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\ServicioController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


//AUTH
    Route::post('/registrar', [AuthController::class, 'registrar']);
    
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/me', [AuthController::class, 'getAuthenticatedUser']);

    });


//USUARIOS
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/usuarios', [UsuariosController::class, 'listarUsuarios']);
    Route::get('/usuarios/{id}', [UsuariosController::class, 'buscarPorId']);

});

//SERVICIOS
Route::get('/services', [ServicioController::class, 'index']);
Route::get('/services/{id}', [ServicioController::class, 'show']);
Route::get('/services/{id}/coordenadas', [ServicioController::class, 'coordenadas']);
 
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/services', [ServicioController::class, 'store']);
    Route::put('/services/{id}', [ServicioController::class, 'update']);
    Route::delete('/services/{id}', [ServicioController::class, 'destroy']);
});

//PAQUETES
use App\Http\Controllers\PaqueteController;
 
Route::get('/packages', [PaqueteController::class, 'index']);
Route::get('/packages/{id}', [PaqueteController::class, 'show']);
 
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/packages', [PaqueteController::class, 'store']);
    Route::put('/packages/{id}', [PaqueteController::class, 'update']);
    Route::delete('/packages/{id}', [PaqueteController::class, 'destroy']);
    Route::post('/packages/{id}/comprar', [PaqueteController::class, 'comprar']);
    Route::post('/packages/{id}/usar-sesion', [PaqueteController::class, 'usarSesion']);
});
 