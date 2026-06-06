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
            'hora_inicio' => 'required|date_format:H:i'
        ]);

        try {
            $reserva = $this->reservaService->crear($request);
            return response()->json($reserva, 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
    
    // GET /reservas
    public function index(Request $request)
    {
        try {
            $reservas = $this->reservaService->listar($request);
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
    
    // PATCH /reservas/{id}/cancelar
    public function cancelar(Request $request, int $id)
    {
        try {
            $reserva = $this->reservaService->cancelar($request, $id);
            return response()->json($reserva, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
    
    public function confirmar(Request $request, int $id)
    {
        try {
            $reserva = $this->reservaService->confirmar($request, $id);
            return response()->json($reserva, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
    
    public function iniciar(Request $request, int $id)
    {
        return response()->json(
            $this->reservaService->iniciar($request, $id)
        );
    }

    public function finalizar(Request $request, int $id)
    {
        return response()->json(
            $this->reservaService->finalizar($request, $id)
        );
    }

    public function noAsistida(Request $request, int $id)
    {
        return response()->json(
            $this->reservaService->noAsistida($request, $id)
        );
    }
}