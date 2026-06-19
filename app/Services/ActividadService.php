<?php

namespace App\Services;

use App\Repositories\ActividadRepository;

class ActividadService
{
    private ActividadRepository $actividadRepository;

    public function __construct(ActividadRepository $actividadRepository)
    {
        $this->actividadRepository = $actividadRepository;
    }

    public function registrar(int $usuarioId, string $accion, string $descripcion): void
    {
        $this->actividadRepository->create([
            'usuario_id' => $usuarioId,
            'accion' => $accion,
            'descripcion' => $descripcion
        ]);
    }

    public function obtenerUltimas(int $cantidad = 50)
    {
        return $this->actividadRepository->getUltimas($cantidad);
    }
}