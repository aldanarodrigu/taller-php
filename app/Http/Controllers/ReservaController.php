<?php

namespace App\Http\Controllers;

use App\Services\ReservaService;
use Exception;
use Illuminate\Http\Request;

class ReservaController extends Controller
{
    public function __construct(
        private ReservaService $reservaService
    ) {}

    // POST /reservas
    public function store(Request $request)
    {
        $request->validate([
            'servicio_id' => 'required|integer',
            'fecha' => 'required|date',
            'dia_semana' => 'required|string|max:255',
            'hora_inicio' => 'required|date_format:H:i',
        ]);

        try {
            $reserva = $this->reservaService->crear($request);
            return response()->json($reserva, 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
    
    // GET /reservas
    public function index()
    {
        try {
            $reservas = $this->reservaService->listar();
            return response()->json($reservas, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
    
    // GET /reservas/{id}
    public function show(int $id)
    {
        try {
            $reserva = $this->reservaService->obtener($id);
            return response()->json($reserva, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
}