<?php

namespace App\Events;

use App\Models\Reserva;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class ReservationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reserva;

    /**
     * Create a new event instance.
     */
    public function __construct(Reserva $reserva)
    {
        $this->reserva = $reserva;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $servicio = $this->reserva->servicio()->first();
        $profesionalId = $servicio ? $servicio->profesional_id : null;

        $channels = [];

        if ($profesionalId) {
            $channels[] = new PrivateChannel('profesional.' . $profesionalId);
        }

        if ($this->reserva->cliente_id) {
            $channels[] = new PrivateChannel('usuario.' . $this->reserva->cliente_id);
        }

        return $channels;
    }

    public function broadcastWith()
    {
        return [
            'reservation_id' => $this->reserva->id,
            'servicio_id' => $this->reserva->servicio_id,
            'fecha' => $this->reserva->fecha,
            'hora_inicio' => $this->reserva->hora_inicio,
            'hora_fin' => $this->reserva->hora_fin,
            'estado' => $this->reserva->estado,
            'cliente_id' => $this->reserva->cliente_id,
        ];
    }
}
