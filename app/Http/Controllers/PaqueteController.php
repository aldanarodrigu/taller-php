<?php

namespace App\Http\Controllers;

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
}