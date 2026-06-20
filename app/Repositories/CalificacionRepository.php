<?php

namespace App\Repositories;

use App\Models\Calificacion;

class CalificacionRepository
{
    public function create(array $data): Calificacion
    {
        return Calificacion::create($data);
    }

    public function findById(int $id): ?Calificacion
    {
        return Calificacion::with(['cliente.user', 'reserva'])->find($id);
    }

    public function findByReserva(int $reservaId): ?Calificacion
    {
        return Calificacion::where('reserva_id', $reservaId)->first();
    }

    public function listarPorServicio(int $servicioId)
    {
        return Calificacion::with(['cliente.user'])
            ->whereHas('reserva', function ($q) use ($servicioId) {
                $q->where('servicio_id', $servicioId);
            })
            ->orderByDesc('created_at')
            ->get();
    }

    public function listarTodas()
    {
        return Calificacion::with(['cliente.user', 'reserva'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function delete(Calificacion $calificacion): void
    {
        $calificacion->delete();
    }

    public function listarPorProfesional(int $profesionalId)
    {
        return Calificacion::with(['cliente.user', 'reserva.servicio'])
            ->whereHas('reserva.servicio', function ($q) use ($profesionalId) {
                $q->where('profesional_id', $profesionalId);
            })
            ->orderByDesc('created_at')
            ->get();
    }
}