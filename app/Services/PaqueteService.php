<?php

namespace App\Services;

use App\Models\Paquete;
use App\Repositories\PaqueteRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class PaqueteService
{
    public function __construct(
        private PaqueteRepository $paqueteRepository
    ) {}

    public function listar()
    {
        return $this->paqueteRepository->listarActivos();
    }

    public function crear(Request $request): Paquete
    {
        $paquete = $this->paqueteRepository->create([
            'nombre'            => $request->nombre,
            'descripcion'       => $request->descripcion,
            'cantidad_sesiones' => $request->cantidad_sesiones,
            'precio'            => $request->precio,
            'vigencia_dias'     => $request->vigencia_dias,
        ]);

        if ($request->has('servicio_ids')) {
            $this->paqueteRepository->sincronizarServicios($paquete, $request->servicio_ids);
        }

        return $paquete->load('servicios');
    }

    public function obtener(int $id): Paquete
    {
        $paquete = $this->paqueteRepository->findById($id);

        if (!$paquete) {
            throw new Exception('Paquete no encontrado', 404);
        }

        return $paquete;
    }

    public function editar(Request $request, int $id): Paquete
    {
        $paquete = $this->obtener($id);

        $paquete = $this->paqueteRepository->update($paquete, $request->only([
            'nombre', 'descripcion', 'cantidad_sesiones',
            'precio', 'vigencia_dias', 'activo',
        ]));

        if ($request->has('servicio_ids')) {
            $this->paqueteRepository->sincronizarServicios($paquete, $request->servicio_ids);
        }

        return $paquete->load('servicios');
    }

    public function eliminar(int $id): void
    {
        $paquete = $this->obtener($id);
        $this->paqueteRepository->delete($paquete);
    }

    public function comprar(Request $request, int $id): void
    {
        $paquete = $this->obtener($id);
        $cliente = $request->user()->cliente;

        if (!$cliente) {
            throw new Exception('El usuario no tiene perfil de cliente', 403);
        }

        if (!$paquete->activo) {
            throw new Exception('Este paquete no está disponible', 422);
        }

        $fechaVencimiento = $paquete->vigencia_dias
            ? Carbon::now()->addDays($paquete->vigencia_dias)->toDateTimeString()
            : null;

        $this->paqueteRepository->comprar($paquete->id, $cliente->id, $fechaVencimiento);
    }

    public function misPaquetes(Request $request)
    {
        $cliente = $request->user()->cliente;

        if (!$cliente) {
            throw new Exception('El usuario no tiene perfil de cliente', 403);
        }

        return $this->paqueteRepository->listarPorCliente($cliente->id);
    }

    public function usarSesion(Request $request, int $id): void
    {
        $paquete = $this->obtener($id);
        $cliente = $request->user()->cliente;

        if (!$cliente) {
            throw new Exception('El usuario no tiene perfil de cliente', 403);
        }

        $clientePivot = $paquete->clientes()->where('cliente_id', $cliente->id)->first();

        if (!$clientePivot) {
            throw new Exception('No tenés este paquete', 404);
        }

        if ($clientePivot->pivot->estado !== 'activo') {
            throw new Exception('El paquete está ' . $clientePivot->pivot->estado, 422);
        }

        if ($clientePivot->pivot->sesiones_disponibles <= 0) {
            throw new Exception('No tenés sesiones disponibles', 422);
        }

        // Verificar vencimiento
        if ($clientePivot->pivot->fecha_vencimiento &&
            Carbon::parse($clientePivot->pivot->fecha_vencimiento)->isPast()) {
            throw new Exception('El paquete está vencido', 422);
        }

        $this->paqueteRepository->usarSesion($paquete->id, $cliente->id);
    }
}