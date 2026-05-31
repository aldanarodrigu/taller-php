<?php

namespace App\Repositories;

use App\Models\Pago;

class PagoRepository
{
    public function create(array $data): Pago
    {
        return Pago::create($data);
    }

    public function findById(int $id): ?Pago
    {
        return Pago::find($id);
    }

    public function findByUsuario(int $usuarioId)
    {
        return Pago::with('reserva')
            ->where('usuario_id', $usuarioId)
            ->orderByDesc('created_at')
            ->get();
    }
}