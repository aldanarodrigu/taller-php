<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

use App\Models\Reserva;

class NuevaReservaNotification extends Notification
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
            'tipo' => 'nueva_reserva',
            'titulo' => 'Nueva reserva',
            'mensaje' => 'Tienes una nueva reserva',
            'reserva_id' => $this->reserva->id,
        ];
    }
}