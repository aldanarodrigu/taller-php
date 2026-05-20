<?php

namespace App\Http\Controllers;

use App\Services\ExcepcionService;
use Exception;
use Illuminate\Http\Request;

class ExcepcionController extends Controller
{
    public function __construct(
        private ExcepcionService $excepcionService
    ) {}

    // POST /excepciones
    public function store(Request $request)
    {
        $request->validate([
            'fecha'       => 'required|date',
            'hora_inicio' => 'nullable|date_format:H:i',
            'hora_fin'    => 'nullable|date_format:H:i',
            'tipo'        => 'required|in:bloqueo,habilitacion_extra,pausa,feriado,licencia',
            'motivo'      => 'nullable|string'
        ]);

        try {
            $excepcion = $this->excepcionService->crear($request);
            return response()->json($excepcion, 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    // GET /excepciones
    public function index()
    {
        try {
            $excepciones = $this->excepcionService->listar();
            return response()->json($excepciones, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    // GET /excepciones/{id}
    public function show(int $id)
    {
        try {
            $excepcion = $this->excepcionService->obtener($id);
            return response()->json($excepcion, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
}