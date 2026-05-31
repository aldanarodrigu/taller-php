<?php

namespace App\Http\Controllers;

use App\Services\LiveKitService;
use Exception;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function __construct(
        private LiveKitService $liveKitService
    ) {}

    // POST /video/reservas/{id}/token
    public function generarToken(Request $request, int $id)
    {
        try {
            $data = $this->liveKitService->generarToken($request, $id);
            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    // POST /video/reservas/{id}/renovar-token
    public function renovarToken(Request $request, int $id)
    {
        try {
            $data = $this->liveKitService->renovarToken($request, $id);
            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    // PATCH /video/reservas/{id}/finalizar
    public function finalizar(Request $request, int $id)
    {
        try {
            $reserva = $this->liveKitService->finalizar($request, $id);
            return response()->json([
                'mensaje' => 'Sesión finalizada correctamente',
                'reserva' => $reserva,
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
}