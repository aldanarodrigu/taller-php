<?php

namespace App\Http\Controllers;

use App\Services\CalificacionService;
use Exception;
use Illuminate\Http\Request;

class CalificacionController extends Controller
{
    public function __construct(
        private CalificacionService $calificacionService
    ) {}

    // GET /reviews
    public function index()
    {
        $calificaciones = $this->calificacionService->listar();
        return response()->json($calificaciones, 200);
    }

    // GET /reviews/{id}
    public function show(int $id)
    {
        try {
            $calificacion = $this->calificacionService->obtener($id);
            return response()->json($calificacion, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    // GET /services/{id}/reviews
    public function porServicio(int $servicioId)
    {
        $calificaciones = $this->calificacionService->listarPorServicio($servicioId);
        return response()->json($calificaciones, 200);
    }

    // POST /reviews
    public function store(Request $request)
    {
        $request->validate([
            'reserva_id' => 'required|integer|exists:reservas,id',
            'puntuacion' => 'required|integer|min:1|max:5',
            'comentario' => 'nullable|string|max:1000',
        ]);

        try {
            $calificacion = $this->calificacionService->crear($request);
            return response()->json($calificacion, 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    // DELETE /reviews/{id}  — solo Admin
    public function destroy(int $id)
    {
        try {
            $this->calificacionService->eliminar($id);
            return response()->json(['mensaje' => 'Reseña eliminada correctamente'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    // GET /professionals/{id}/reviews
    public function porProfesional(int $profesionalId)
    {
        $calificaciones = $this->calificacionService->listarPorProfesional($profesionalId);
        return response()->json($calificaciones, 200);
    }   
}