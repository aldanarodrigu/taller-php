<?php

namespace App\Repositories;

use App\Models\Actividad;

class ActividadRepository
{
    public function create(array $data): Actividad
    {
        return Actividad::create($data);
    }

    public function getUltimas(int $cantidad = 50)
    {
        return Actividad::with('usuario')
            ->latest()
            ->take($cantidad)
            ->get();
    }
}