<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\Reserva;

class ReservationStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reservationId;
    public $oldStatus;
    public $newStatus;
    public $changedBy;

    public function __construct(int $reservationId, string $oldStatus, string $newStatus, ?int $changedBy = null)
    {
        $this->reservationId = $reservationId;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->changedBy = $changedBy;
    }

    public function broadcastOn()
    {
        $reserva = Reserva::find($this->reservationId);
        $channels = [];

        if ($reserva) {
            $servicio = $reserva->servicio()->first();
            $profesionalId = $servicio ? $servicio->profesional_id : null;
            if ($profesionalId) {
                $channels[] = new PrivateChannel('profesional.' . $profesionalId);
            }

            if ($reserva->cliente_id) {
                $channels[] = new PrivateChannel('usuario.' . $reserva->cliente_id);
            }
        }

        return $channels;
    }

    public function broadcastWith()
    {
        return [
            'reservation_id' => $this->reservationId,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'changed_by' => $this->changedBy,
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}
