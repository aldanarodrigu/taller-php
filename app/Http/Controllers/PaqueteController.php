<?php

namespace App\Http\Controllers;

use App\Models\PaqueteCliente;
use App\Services\PaqueteService;
use Exception;
use Illuminate\Http\Request;

class PaqueteController extends Controller
{
    public function __construct(
        private PaqueteService $paqueteService
    ) {}

    // GET /packages/mis-paquetes
    public function misPaquetes(Request $request)
    {
        try {
            $paquetes = $this->paqueteService->misPaquetes($request);
            return response()->json($paquetes, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    // GET /packages
    public function index()
    {
        $paquetes = $this->paqueteService->listar();
        return response()->json($paquetes, 200);
    }

    // POST /packages
    public function store(Request $request)
    {
        $request->validate([
            'nombre'            => 'required|string|max:255',
            'cantidad_sesiones' => 'required|integer|min:1',
            'precio'            => 'required|numeric|min:0',
            'descripcion'       => 'nullable|string',
            'vigencia_dias'     => 'nullable|integer|min:1',
            'servicio_ids'      => 'nullable|array',
            'servicio_ids.*'    => 'integer|exists:servicios,id',
        ]);

        try {
            $paquete = $this->paqueteService->crear($request);
            return response()->json($paquete, 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    // GET /packages/{id}
    public function show(int $id)
    {
        try {
            $paquete = $this->paqueteService->obtener($id);
            return response()->json($paquete, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    // PUT /packages/{id}
    public function update(Request $request, int $id)
    {
        $request->validate([
            'nombre'            => 'sometimes|string|max:255',
            'cantidad_sesiones' => 'sometimes|integer|min:1',
            'precio'            => 'sometimes|numeric|min:0',
            'descripcion'       => 'nullable|string',
            'vigencia_dias'     => 'nullable|integer|min:1',
            'activo'            => 'sometimes|boolean',
            'servicio_ids'      => 'nullable|array',
            'servicio_ids.*'    => 'integer|exists:servicios,id',
        ]);

        try {
            $paquete = $this->paqueteService->editar($request, $id);
            return response()->json($paquete, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    // DELETE /packages/{id}
    public function destroy(int $id)
    {
        try {
            $this->paqueteService->eliminar($id);
            return response()->json(['mensaje' => 'Paquete eliminado correctamente'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    // POST /packages/{id}/comprar
    public function comprar(Request $request, int $id)
    {
        try {
            $this->paqueteService->comprar($request, $id);
            return response()->json(['mensaje' => 'Paquete comprado correctamente'], 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    // GET /packages/mis-paquetes-profesional
    public function misPaquetesProfesional(Request $request)
    {
        $profesional = $request->user()->profesional;

        if (!$profesional) {
            return response()->json(['error' => 'No tenés perfil de profesional'], 403);
        }

        $paquetes = \App\Models\Paquete::with('servicios')
            ->whereHas('servicios', fn($q) => $q->where('profesional_id', $profesional->id))
            ->get();

        return response()->json($paquetes);
    }

    // GET /packages/para-servicio/{servicio_id}
    public function paraServicio(Request $request, int $servicioId)
    {
        $cliente = $request->user()->cliente;

        if (!$cliente) {
            return response()->json(['error' => 'No tenés perfil de cliente'], 403);
        }

        $paquetes = PaqueteCliente::where('cliente_id', $cliente->id)
            ->where('estado', 'activo')
            ->where('sesiones_disponibles', '>', 0)
            ->whereHas('paquete.servicios', fn($q) => $q->where('servicios.id', $servicioId))
            ->with('paquete:id,nombre,cantidad_sesiones')
            ->get()
            ->map(fn($pc) => [
                'paquete_cliente_id'   => $pc->id,
                'nombre'               => $pc->paquete->nombre,
                'sesiones_disponibles' => $pc->sesiones_disponibles,
                'sesiones_usadas'      => $pc->sesiones_usadas,
                'fecha_vencimiento'    => $pc->fecha_vencimiento,
            ]);

        return response()->json($paquetes);
    }

    // POST /packages/{id}/usar-sesion
    public function usarSesion(Request $request, int $id)
    {
        try {
            $this->paqueteService->usarSesion($request, $id);
            return response()->json(['mensaje' => 'Sesión utilizada correctamente'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function porProfesional(int $id)
    {
        try {
            $paquetes = $this->paqueteService->listarPorProfesional($id);

            return response()->json($paquetes, 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }
}