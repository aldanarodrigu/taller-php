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

    // POST /pagos
    public function store(Request $request)
    {
        $request->validate([
            'monto' => 'required|numeric|min:0'
        ]);

        try {
            $pago = $this->pagoService->crear($request);
            return response()->json($pago, 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    // GET /pagos
    public function index()
    {
        try {
            $pagos = $this->pagoService->listar();
            return response()->json($pagos, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    // GET /pagos/{id}
    public function show(int $id)
    {
        try {
            $pago = $this->pagoService->obtener($id);
            return response()->json($pago, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
}