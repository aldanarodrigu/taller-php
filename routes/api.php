<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\DisponibilidadController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\ExcepcionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CalificacionController;
use App\Http\Controllers\PaqueteController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\ProfesionalController;

//AUTH
    Route::post('/registrar', [AuthController::class, 'registrar']);
    
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/me', [AuthController::class, 'getAuthenticatedUser']);
        Route::post('/logout', [AuthController::class, 'logout']);
// Health check
Route::get('/health', function () {
    return response()->json(['ok' => true]);
});


    });


//USUARIOS
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/usuarios', [UsuariosController::class, 'listarUsuarios']);
    Route::get('/usuarios/{id}', [UsuariosController::class, 'buscarPorId']);
    Route::put('/usuarios/{id}', [UsuariosController::class, 'editarUsuario']);
    
});


//SERVICIOS
Route::get('/services', [ServicioController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/services/me', [ServicioController::class, 'misServicios']);
    Route::post('/services', [ServicioController::class, 'store']);
    Route::get('/services/con-profesional', [ServicioController::class, 'indexConProfesional']);
    Route::put('/services/{id}', [ServicioController::class, 'update']);
    Route::delete('/services/{id}', [ServicioController::class, 'destroy']);
});

Route::get('/services/{id}', [ServicioController::class, 'show']);
Route::get('/services/{id}/coordenadas', [ServicioController::class, 'coordenadas']);
 

//PAQUETES
Route::get('/packages', [PaqueteController::class, 'index']);
Route::get('/packages/{id}', [PaqueteController::class, 'show']);
 
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/packages', [PaqueteController::class, 'store']);
    Route::put('/packages/{id}', [PaqueteController::class, 'update']);
    Route::delete('/packages/{id}', [PaqueteController::class, 'destroy']);
    Route::post('/packages/{id}/comprar', [PaqueteController::class, 'comprar']);
    Route::post('/packages/{id}/usar-sesion', [PaqueteController::class, 'usarSesion']);
});


// DISPONIBILIDAD
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/disponibilidades', [DisponibilidadController::class, 'store']);
});

Route::get('/disponibilidades/profesional/{id}', [DisponibilidadController::class, 'listarPorProfesional']);
 

// RESERVAS
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/reservas', [ReservaController::class, 'store']);
    Route::patch('/reservas/{id}/cancelar', [ReservaController::class, 'cancelar']);
});

Route::get('/reservas', [ReservaController::class, 'index']);
Route::get('/reservas/{id}', [ReservaController::class, 'show']);


//PAGOS
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/pagos', [PagoController::class, 'store']);
    Route::get('/pagos', [PagoController::class, 'index']);
    Route::get('/pagos/{id}', [PagoController::class, 'show']);
    Route::post('/pagos/{id}/reintentar', [PagoController::class, 'reintentar']);
});


// EXCEPCIONES
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/excepciones', [ExcepcionController::class, 'store']);
});

Route::get('/excepciones', [ExcepcionController::class, 'index']);
Route::get('/excepciones/{id}', [ExcepcionController::class, 'show']);


//RESEÑAS
Route::get('/reviews', [CalificacionController::class, 'index']);
Route::get('/reviews/{id}', [CalificacionController::class, 'show']);
Route::get('/services/{id}/reviews', [CalificacionController::class, 'porServicio']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/reviews', [CalificacionController::class, 'store']);
    Route::delete('/reviews/{id}', [CalificacionController::class, 'destroy']);
});

//VIDEOLLAMADAS
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/video/reservas/{id}/token', [VideoController::class, 'generarToken']);
    Route::post('/video/reservas/{id}/renovar-token', [VideoController::class, 'renovarToken']);
    Route::patch('/video/reservas/{id}/finalizar', [VideoController::class, 'finalizar']);
});

// AGENDA
Route::get('/agenda/profesional/{id}', [AgendaController::class, 'profesional']);

// NOTIFICACIONES
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notificaciones',                       [NotificacionController::class, 'index']);
    Route::post('/notificaciones/leer-todas',           [NotificacionController::class, 'markAllAsRead']);
    Route::get('/notificaciones/{id}',                  [NotificacionController::class, 'show']);
    Route::patch('/notificaciones/{id}/leer',           [NotificacionController::class, 'markAsRead']);
    Route::delete('/notificaciones/{id}',               [NotificacionController::class, 'destroy']);
});

//PROFESIONALES
Route::middleware('auth:sanctum')->group(function () {
    Route::put('/profesional', [ProfesionalController::class, 'editarProfesional']);
});