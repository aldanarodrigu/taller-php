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
    private int $profesionalId; 

    public function __construct(Reserva $reserva)
    {
        $this->reserva = $reserva;
        $this->profesionalId = $reserva->servicio->profesional_id; 
    }

    public function broadcastOn(): array
    {
        $channels = [];

        if ($this->profesionalId) {
            $channels[] = new PrivateChannel('profesional.' . $this->profesionalId);
        }

        if ($this->reserva->cliente_id) {
            $channels[] = new PrivateChannel('usuario.' . $this->reserva->cliente_id);
        }

        return $channels;
    }

    public function broadcastWith(): array
    {
        return [
            'reservation_id' => $this->reserva->id,
            'servicio_id'    => $this->reserva->servicio_id,
            'fecha'          => $this->reserva->fecha,
            'hora_inicio'    => $this->reserva->hora_inicio,
            'hora_fin'       => $this->reserva->hora_fin,
            'estado'         => $this->reserva->estado,
            'cliente_id'     => $this->reserva->cliente_id,
        ];
    }
}