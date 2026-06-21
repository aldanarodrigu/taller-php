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
            $status = (int) $e->getCode();

            if ($status < 100 || $status > 599) {
                $status = 500;
            }

            return response()->json([
                'error' => $e->getMessage(),
            ], $status);
        }
    }

    // GET /excepciones
    public function index(Request $request)
    {
        try {
            $excepciones = $this->excepcionService->listar($request);
            return response()->json($excepciones, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    // DELETE /excepciones/{id}
    public function destroy(Request $request, int $id)
    {
        try {
            $profesional = $request->user()->profesional;

            if (!$profesional) {
                return response()->json(['error' => 'No tenés perfil de profesional'], 403);
            }

            $excepcion = \App\Models\Excepcion::find($id);

            if (!$excepcion) {
                return response()->json(['error' => 'Excepción no encontrada'], 404);
            }

            if ($excepcion->profesional_id !== $profesional->id) {
                return response()->json(['error' => 'No tenés permiso para eliminar esta excepción'], 403);
            }

            $excepcion->delete();

            return response()->json(null, 204);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
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