<?php

namespace App\Services;

use App\Models\Servicio;
use App\Repositories\ServicioRepository;
use App\Services\ActividadService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServicioService
{
    public function __construct(
        private ServicioRepository $servicioRepository,
        private ActividadService $actividadService,
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

        $videollamada = $request->has('videollamada')
            ? $this->toDatabaseBoolean($request->boolean('videollamada'))
            : $this->toDatabaseBoolean(false);

        $servicio = $this->servicioRepository->create([
            'profesional_id'            => $profesional->id,
            'nombre'                    => $request->nombre,
            'descripcion'               => $request->descripcion,
            'tipo'                      => $request->tipo,
            'modalidad'                 => $request->modalidad,
            'precio'                    => $request->precio,
            'duracion_minutos'          => $request->duracion_minutos,
            'videollamada'              => $videollamada,
            'cancelacion_horas_minimas' => $request->cancelacion_horas_minimas,
            'direccion'                 => $request->direccion,
            'latitud'                   => $request->latitud,
            'longitud'                  => $request->longitud,
        ]);

        $this->actividadService->registrar(
            $request->user()->id,
            'CREAR_SERVICIO',
            'Creó el servicio "' . $servicio->nombre . '"'
        );

        return $servicio;
    }

    public function obtenerServiciosUsuario(Request $request)
    {
        $profesional = $request->user()->profesional;

        if (!$profesional) {
            throw new Exception(
                'El usuario no tiene perfil de profesional',
                403
            );
        }

        return $this->servicioRepository
            ->findByProfesional($profesional->id);
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

        $data = $request->only([
            'nombre', 'descripcion', 'tipo', 'modalidad',
            'precio', 'duracion_minutos', 'activo',
            'videollamada', 'cancelacion_horas_minimas',
            'direccion', 'latitud', 'longitud',
        ]);

        if ($request->has('activo')) {
            $data['activo'] = $this->toDatabaseBoolean($request->boolean('activo'));
        }

        if ($request->has('videollamada')) {
            $data['videollamada'] = $this->toDatabaseBoolean($request->boolean('videollamada'));
        }

        return $this->servicioRepository->update($servicio, $data);
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

    private function toDatabaseBoolean(bool $value): bool|string
    {
        return DB::connection()->getDriverName() === 'pgsql'
            ? ($value ? 'true' : 'false')
            : $value;
    }

    public function listarPorProfesional(int $profesionalId)
    {
        return $this->servicioRepository->findByProfesional($profesionalId);
    }
}