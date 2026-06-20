<?php

namespace App\Repositories;

use App\Models\Paquete;

class PaqueteRepository
{
    public function create(array $data): Paquete
    {
        return Paquete::create($data);
    }

    public function findById(int $id): ?Paquete
    {
        return Paquete::with('servicios')->find($id);
    }

    public function update(Paquete $paquete, array $data): Paquete
    {
        $paquete->update($data);
        return $paquete->fresh();
    }

    public function delete(Paquete $paquete): void
    {
        $paquete->delete();
    }

    public function listarActivos()
    {
        return Paquete::with('servicios')->whereRaw('"activo" IS TRUE')->get();
    }

    public function sincronizarServicios(Paquete $paquete, array $servicioIds): void
    {
        $paquete->servicios()->sync($servicioIds);
    }

    public function comprar(int $paqueteId, int $clienteId, ?string $fechaVencimiento): void
    {
        $paquete = $this->findById($paqueteId);

        $paquete->clientes()->attach($clienteId, [
            'sesiones_disponibles' => $paquete->cantidad_sesiones,
            'sesiones_usadas'      => 0,
            'estado'               => 'activo',
            'fecha_compra'         => now(),
            'fecha_vencimiento'    => $fechaVencimiento,
        ]);
    }

    public function listarPorCliente(int $clienteId)
    {
        return Paquete::with('servicios')
            ->whereHas('clientes', fn($q) => $q->where('cliente_id', $clienteId))
            ->with(['clientes' => fn($q) => $q->where('cliente_id', $clienteId)])
            ->get();
    }

    public function usarSesion(int $paqueteId, int $clienteId): void
    {
        $paquete = $this->findById($paqueteId);
        $pivot   = $paquete->clientes()->where('cliente_id', $clienteId)->first()?->pivot;

        if (!$pivot) {
            throw new \Exception('El cliente no tiene este paquete', 404);
        }

        $paquete->clientes()->updateExistingPivot($clienteId, [
            'sesiones_disponibles' => $pivot->sesiones_disponibles - 1,
            'sesiones_usadas'      => $pivot->sesiones_usadas + 1,
            'estado'               => $pivot->sesiones_disponibles - 1 <= 0 ? 'agotado' : 'activo',
        ]);
    }
}