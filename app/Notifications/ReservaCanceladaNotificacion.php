<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

use App\Models\Reserva;

class ReservaCanceladaNotificacion extends Notification
{
    use Queueable;

    private Reserva $reserva;

    public function __construct(Reserva $reserva)
    {
        $this->reserva = $reserva;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tipo' => 'reserva_cancelada',
            'titulo' => 'Reserva Cancelada',
            'mensaje' => "{$this->reserva->cliente->user->name} canceló una reserva.",
            'reserva_id' => $this->reserva->id,
        ];
    }
}