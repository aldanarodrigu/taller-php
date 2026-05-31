<?php

namespace App\Http\Controllers;

use App\Services\PagoService;
use Exception;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    public function __construct(
        private PagoService $pagoService
    ) {}

    // POST /pagos — iniciar pago de una reserva
    public function store(Request $request)
    {
        $request->validate([
            'reserva_id' => 'required|integer|exists:reservas,id',
            'monto'      => 'required|numeric|min:0',
        ]);

        try {
            $pago = $this->pagoService->crear($request);
            return response()->json($pago, 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    // GET /pagos — listar pagos del usuario logueado
    public function index(Request $request)
    {
        $pagos = $this->pagoService->listarDelUsuario($request);
        return response()->json($pagos, 200);
    }

    // GET /pagos/{id} — obtener estado de un pago
    public function show(int $id)
    {
        try {
            $pago = $this->pagoService->obtener($id);
            return response()->json($pago, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    // POST /pagos/{id}/reintentar — reintentar pago rechazado
    public function reintentar(Request $request, int $id)
    {
        try {
            $pago = $this->pagoService->reintentar($request, $id);
            return response()->json($pago, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
}