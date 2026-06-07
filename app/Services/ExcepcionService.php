<?php

namespace App\Services;

use App\Models\Excepcion;
use App\Repositories\ExcepcionRepository;
use Exception;
use Illuminate\Http\Request;

class ExcepcionService
{
    public function __construct(
        private ExcepcionRepository $excepcionRepository
    ) {}

    public function crear(Request $request): Excepcion
    {
        $profesional = $request->user()->profesional;

        if (!$profesional) {
            throw new Exception('El usuario no tiene perfil de profesional', 403);
        }

        if (($request->hora_inicio === null && $request->hora_fin !== null) ||
            ($request->hora_inicio !== null && $request->hora_fin === null)) {
            throw new Exception('Se deben completar hora_inicio y hora_fin juntas', 422);
        }

        if ($request->hora_inicio !== null && $request->hora_fin !== null) {
            if ($request->hora_fin <= $request->hora_inicio) {
                throw new Exception('La hora de finalización debe ser mayor a la hora de inicio', 422);
            }
        }

        return $this->excepcionRepository->create([
            'profesional_id' => $profesional->id,
            'fecha' => $request->fecha,
            'hora_inicio' => $request->hora_inicio,
            'hora_fin' => $request->hora_fin,
            'tipo' => $request->tipo,
            'motivo' => $request->motivo,
        ]);
    }

    public function listar(Request $request)
    {
        $profesional = $request->user()->profesional;

        if (!$profesional) {
            throw new Exception('El usuario no tiene perfil de profesional', 403);
        }

        return $this->excepcionRepository->findByProfesionalId($profesional->id);
    }

    public function obtener(int $id): Excepcion
    {
        $excepcion = $this->excepcionRepository->findById($id);

        if (!$excepcion) {
            throw new Exception('Excepción no encontrada', 404);
        }

        return $excepcion;
    }
}