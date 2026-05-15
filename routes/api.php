<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsuariosController;

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

    Route::get('/usuarios/{id}', [UsuariosController::class, 'buscarPorId']);

});