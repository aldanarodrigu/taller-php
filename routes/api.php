<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\DisponibilidadController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\ExcepcionController;
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


//DISPONIBILIDAD

Route::post('/disponibilidades', [DisponibilidadController::class, 'store']);
Route::get('/disponibilidades/profesional/{id}', [DisponibilidadController::class, 'listarPorProfesional']);
 

//RESERVAS
Route::post('/reservas', [ReservaController::class, 'store']);
Route::get('/reservas', [ReservaController::class, 'index']);
Route::get('/reservas/{id}', [ReservaController::class, 'show']);
Route::patch('/reservas/{id}/cancelar', [ReservaController::class, 'cancelar']);


//PAGOS
Route::post('/pagos', [PagoController::class, 'store']);
Route::get('/pagos', [PagoController::class, 'index']);
Route::get('/pagos/{id}', [PagoController::class, 'show']);


//EXCEPCIONES
Route::post('/excepciones', [ExcepcionController::class, 'store']);
Route::get('/excepciones', [ExcepcionController::class, 'index']);
Route::get('/excepciones/{id}', [ExcepcionController::class, 'show']);