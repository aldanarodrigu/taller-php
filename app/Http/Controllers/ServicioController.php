<?php

namespace App\Http\Controllers;

use App\Services\ServicioService;
use App\Models\Servicio;
use Exception;
use Illuminate\Http\Request;

class ServicioController extends Controller
{
    public function __construct(
        private ServicioService $servicioService
    ) {}

    // GET /services
    public function index(Request $request)
    {
        $filtros = $request->only([
            'tipo', 'modalidad', 'precio_min', 'precio_max',
            'nombre', 'lat', 'lng', 'radio_km',
        ]);

        $servicios = $this->servicioService->listar($filtros);
        return response()->json($servicios, 200);
    }

    // POST /services
    public function store(Request $request)
    {
        $request->validate([
            'nombre'                    => 'required|string|max:255',
            'tipo'                      => 'required|string|max:100',
            'modalidad'                 => 'required|in:presencial,virtual,hibrida',
            'precio'                    => 'required|numeric|min:0',
            'duracion_minutos'          => 'required|integer|min:15',
            'videollamada'              => 'nullable|boolean',
            'cancelacion_horas_minimas' => 'nullable|integer|min:0',
            'descripcion'               => 'nullable|string',
            'direccion'                 => 'nullable|string',
            'latitud'                   => 'nullable|numeric|between:-90,90',
            'longitud'                  => 'nullable|numeric|between:-180,180',
        ]);

        try {
            $servicio = $this->servicioService->crear($request);
            return response()->json($servicio, 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    // GET /services/{id}
    public function show(int $id)
    {
        try {
            $servicio = $this->servicioService->obtener($id);
            return response()->json($servicio, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    // PUT /services/{id}
    public function update(Request $request, int $id)
    {
        $request->validate([
            'nombre'                    => 'sometimes|string|max:255',
            'tipo'                      => 'sometimes|string|max:100',
            'modalidad'                 => 'sometimes|in:presencial,virtual,hibrida',
            'precio'                    => 'sometimes|numeric|min:0',
            'duracion_minutos'          => 'sometimes|integer|min:15',
            'activo'                    => 'sometimes|boolean',
            'videollamada'              => 'sometimes|boolean',
            'cancelacion_horas_minimas' => 'nullable|integer|min:0',
            'descripcion'               => 'nullable|string',
            'direccion'                 => 'nullable|string',
            'latitud'                   => 'nullable|numeric|between:-90,90',
            'longitud'                  => 'nullable|numeric|between:-180,180',
        ]);

        try {
            $servicio = $this->servicioService->editar($request, $id);
            return response()->json($servicio, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    // DELETE /services/{id}
    public function destroy(Request $request, int $id)
    {
        try {
            $this->servicioService->eliminar($request, $id);
            return response()->json(['mensaje' => 'Servicio eliminado correctamente'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    // GET /services/{id}/coordenadas
    public function coordenadas(int $id)
    {
        try {
            $coords = $this->servicioService->obtenerCoordenadas($id);
            return response()->json($coords, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    // GET /services/me
    public function misServicios(Request $request)
    {
        try {
            $servicios = $this->servicioService
                ->obtenerServiciosUsuario($request);

            return response()->json($servicios, 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }

    // GET /services/con-profesional
    public function indexConProfesional()
    {
        $servicios = Servicio::with('profesional.user')->paginate(20);
        return response()->json($servicios, 200);
    }

}