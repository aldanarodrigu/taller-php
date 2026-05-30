<?php

namespace App\Services;

use App\Models\Calificacion;
use App\Models\Reserva;
use App\Repositories\CalificacionRepository;
use Exception;
use Illuminate\Http\Request;

class CalificacionService
{
    public function __construct(
        private CalificacionRepository $calificacionRepository
    ) {}

    public function listar()
    {
        return $this->calificacionRepository->listarTodas();
    }

    public function listarPorServicio(int $servicioId)
    {
        return $this->calificacionRepository->listarPorServicio($servicioId);
    }

    public function obtener(int $id): Calificacion
    {
        $calificacion = $this->calificacionRepository->findById($id);

        if (!$calificacion) {
            throw new Exception('Calificación no encontrada', 404);
        }

        return $calificacion;
    }

    public function crear(Request $request): Calificacion
    {
        $cliente = $request->user()->cliente;

        if (!$cliente) {
            throw new Exception('El usuario no tiene perfil de cliente', 403);
        }

        $reserva = Reserva::find($request->reserva_id);

        if (!$reserva) {
            throw new Exception('Reserva no encontrada', 404);
        }

        if ($reserva->cliente_id !== $cliente->id) {
            throw new Exception('No podés calificar una reserva que no es tuya', 403);
        }

        if ($reserva->estado !== 'finalizada') {
            throw new Exception('Solo podés calificar reservas finalizadas', 422);
        }

        $existente = $this->calificacionRepository->findByReserva($request->reserva_id);

        if ($existente) {
            throw new Exception('Ya calificaste esta reserva', 422);
        }

        return $this->calificacionRepository->create([
            'reserva_id' => $request->reserva_id,
            'cliente_id' => $cliente->id,
            'puntuacion' => $request->puntuacion,
            'comentario' => $request->comentario,
        ]);
    }

    public function eliminar(int $id): void
    {
        $calificacion = $this->obtener($id);
        $this->calificacionRepository->delete($calificacion);
    }
}