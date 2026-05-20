<?php

namespace App\Repositories;

use App\Models\Excepcion;

class ExcepcionRepository
{
    public function create(array $data): Excepcion
    {
        return Excepcion::create($data);
    }

    public function findById(int $id)
    {
        return Excepcion::where('id', $id)->first();
    }

    public function findAll()
    {
        return Excepcion::all();
    }

    public function findByProfesionalAndFecha(int $profesionalId, string $fecha)
    {
        return Excepcion::where('profesional_id', $profesionalId)->where('fecha', $fecha)->get();
    }
}