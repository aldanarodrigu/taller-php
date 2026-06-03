<?php

namespace App\Http\Controllers;

use App\Services\AgendaService;
use Exception;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    public function __construct(
        private AgendaService $agendaService
    ) {}

    // GET /agenda/profesional/{id}?fecha=2026-06-03&servicio_id=10
    public function profesional(Request $request, int $id)
    {
        $request->validate([
            'fecha' => 'required|date',
            'servicio_id' => 'required|integer'
        ]);

        try {
            $agenda = $this->agendaService->obtenerAgendaProfesional(
                $id,
                $request->fecha,
                $request->servicio_id
            );

            return response()->json($agenda, 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }
}