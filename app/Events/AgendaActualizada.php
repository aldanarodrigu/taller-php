<?php

namespace App\Events;

use App\Models\Reserva;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AgendaActualizada implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Reserva $reserva,
        public string $accion
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('agenda.profesional.' . $this->reserva->servicio->profesional_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'agenda.actualizada';
    }

    public function broadcastWith(): array
    {
        return [
            'accion' => $this->accion,
            'reserva_id' => $this->reserva->id,
            'servicio_id' => $this->reserva->servicio_id,
            'profesional_id' => $this->reserva->servicio->profesional_id,
            'fecha' => $this->reserva->fecha,
            'hora_inicio' => $this->reserva->hora_inicio,
            'hora_fin' => $this->reserva->hora_fin,
            'estado' => $this->reserva->estado,
        ];
    }
}