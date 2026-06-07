<?php

namespace App\Services;

use App\Models\Pago;
use App\Models\Reserva;
use App\Repositories\PagoRepository;
use Exception;
use Illuminate\Http\Request;

use App\Events\AgendaActualizada;
use App\Jobs\NotificarCambioReserva;

class PagoService
{
    public function __construct(
        private PagoRepository $pagoRepository
    ) {}

    public function crear(Request $request): Pago
    {
        $usuario = $request->user();

        $reserva = Reserva::find($request->reserva_id);

        if (!$reserva) {
            throw new Exception('Reserva no encontrada', 404);
        }

        if ($usuario->cliente && $reserva->cliente_id !== $usuario->cliente->id) {
            throw new Exception('No podés pagar una reserva que no es tuya', 403);
        }

        if (!in_array($reserva->estado, ['pendiente', 'confirmada'])) {
            throw new Exception('La reserva no está en un estado pagable', 422);
        }

        $pago = $this->pagoRepository->create([
            'reserva_id' => $reserva->id,
            'usuario_id' => $usuario->id,
            'monto'      => $request->monto,
            'metodo'     => 'simulado',
            'estado'     => 'aprobado',
            'fecha_pago' => now()->toDateString(),
            'hora_pago'  => now()->toTimeString(),
        ]);

        $reserva->update([
            'pago_id' => $pago->id,
            'estado'  => 'pagada',
        ]);
        
        $reserva->refresh();

        event(new AgendaActualizada($reserva, 'pagada'));
        NotificarCambioReserva::dispatch($reserva->id, 'pagada');

        return $pago->load('reserva');
    }

    public function obtener(int $id): Pago
    {
        $pago = $this->pagoRepository->findById($id);

        if (!$pago) {
            throw new Exception('Pago no encontrado', 404);
        }

        return $pago->load('reserva');
    }

    public function listarDelUsuario(Request $request)
    {
        return $this->pagoRepository->findByUsuario($request->user()->id);
    }

    public function reintentar(Request $request, int $id): Pago
    {
        $pago = $this->pagoRepository->findById($id);

        if (!$pago) {
            throw new Exception('Pago no encontrado', 404);
        }

        if ($pago->usuario_id !== $request->user()->id) {
            throw new Exception('No podés reintentar un pago que no es tuyo', 403);
        }

        if ($pago->estado !== 'rechazado') {
            throw new Exception('Solo se pueden reintentar pagos rechazados', 422);
        }

        $pago->update([
            'estado'     => 'aprobado',
            'fecha_pago' => now()->toDateString(),
            'hora_pago'  => now()->toTimeString(),
        ]);

        if ($pago->reserva_id) {
            $reserva = $pago->reserva;

            $reserva->update([
                'pago_id' => $pago->id,
                'estado' => 'pagada',
            ]);

            $reserva->refresh();

            event(new AgendaActualizada($reserva, 'pagada'));
            NotificarCambioReserva::dispatch($reserva->id, 'pagada');
        }

        return $pago->load('reserva');
    }
}