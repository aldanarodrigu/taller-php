<?php

namespace App\Services;

use App\Models\Servicio;
use App\Repositories\ServicioRepository;
use Exception;
use Illuminate\Http\Request;

class ServicioService
{
    public function __construct(
        private ServicioRepository $servicioRepository
    ) {}

    public function listar(array $filtros)
    {
        return $this->servicioRepository->listarConFiltros($filtros);
    }

    public function crear(Request $request): Servicio
    {
        $profesional = $request->user()->profesional;

        if (!$profesional) {
            throw new Exception('El usuario no tiene perfil de profesional', 403);
        }

        return $this->servicioRepository->create([
            'profesional_id'           => $profesional->id,
            'nombre'                   => $request->nombre,
            'descripcion'              => $request->descripcion,
            'tipo'                     => $request->tipo,
            'modalidad'                => $request->modalidad,
            'precio'                   => $request->precio,
            'duracion_minutos'         => $request->duracion_minutos,
            'videollamada'             => $request->videollamada ?? false,
            'cancelacion_horas_minimas' => $request->cancelacion_horas_minimas,
            'direccion'                => $request->direccion,
            'latitud'                  => $request->latitud,
            'longitud'                 => $request->longitud,
        ]);
    }

    public function obtener(int $id): Servicio
    {
        $servicio = $this->servicioRepository->findById($id);

        if (!$servicio) {
            throw new Exception('Servicio no encontrado', 404);
        }

        return $servicio;
    }

    public function editar(Request $request, int $id): Servicio
    {
        $servicio = $this->obtener($id);
        $this->verificarPropietario($request, $servicio);

        return $this->servicioRepository->update($servicio, $request->only([
            'nombre', 'descripcion', 'tipo', 'modalidad',
            'precio', 'duracion_minutos', 'activo',
            'videollamada', 'cancelacion_horas_minimas',
            'direccion', 'latitud', 'longitud',
        ]));
    }

    public function eliminar(Request $request, int $id): void
    {
        $servicio = $this->obtener($id);
        $this->verificarPropietario($request, $servicio);
        $this->servicioRepository->delete($servicio);
    }

    public function obtenerCoordenadas(int $id): array
    {
        $servicio = $this->obtener($id);

        if (!$servicio->latitud || !$servicio->longitud) {
            throw new Exception('Este servicio no tiene ubicación registrada', 422);
        }

        return [
            'latitud'   => $servicio->latitud,
            'longitud'  => $servicio->longitud,
            'direccion' => $servicio->direccion,
        ];
    }

    private function verificarPropietario(Request $request, Servicio $servicio): void
    {
        $profesional = $request->user()->profesional;

        if (!$profesional || $servicio->profesional_id !== $profesional->id) {
            throw new Exception('No tenés permiso para modificar este servicio', 403);
        }
    }
}