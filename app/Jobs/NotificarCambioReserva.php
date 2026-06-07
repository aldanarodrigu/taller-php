<?php

namespace App\Jobs;

use App\Models\Reserva;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class NotificarCambioReserva implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $reservaId,
        public string $accion
    ) {}

    public function handle(): void
    {
        $reserva = Reserva::with([
            'cliente.user',
            'servicio.profesional.user'
        ])->find($this->reservaId);

        if (!$reserva) {
            Log::warning('No se pudo notificar cambio de reserva: reserva no encontrada', [
                'reserva_id' => $this->reservaId,
                'accion' => $this->accion,
            ]);

            return;
        }

        Log::info('Notificación asincrónica de reserva procesada', [
            'accion' => $this->accion,
            'reserva_id' => $reserva->id,
            'estado' => $reserva->estado,
            'fecha' => $reserva->fecha,
            'hora_inicio' => $reserva->hora_inicio,
            'cliente_id' => $reserva->cliente_id,
            'servicio_id' => $reserva->servicio_id,
        ]);
    }
}