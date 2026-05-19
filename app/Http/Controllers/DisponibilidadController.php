<?php

namespace App\Http\Controllers;

use App\Services\DisponibilidadService;
use Exception;
use Illuminate\Http\Request;

class DisponibilidadController extends Controller
{
    public function __construct(
        private DisponibilidadService $disponibilidadService
    ) {}
    
    // GET /disponibilidades/profesional/{id}
    public function listarPorProfesional(int $id)
    {
        try {
            $disponibilidades = $this->disponibilidadService->listarPorProfesional($id);
            return response()->json($disponibilidades, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
    
    // POST /disponibilidades
    public function store(Request $request)
    {
        $request->validate([
            'dia_semana'            => 'required|string|max:255',
            'hora_inicio'           => 'required|date_format:H:i',
            'hora_fin'              => 'required|date_format:H:i',
            'buffer'                => 'nullable|integer|min:0', 
            'hora_inicio_pausa'     => 'nullable|date_format:H:i',
            'hora_fin_pausa'        => 'nullable|date_format:H:i'
        ]);

        try {
            $disponibilidad  = $this->disponibilidadService->crear($request);
            return response()->json($disponibilidad, 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
}
