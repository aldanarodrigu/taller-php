<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

use App\Models\Reserva;

class ReservaConfirmadaNotificacion extends Notification
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
            'tipo' => 'reserva_confirmada',
            'titulo' => 'Reserva Confirmada',
            'mensaje' => "Tu reserva ha sido confirmada.",
            'reserva_id' => $this->reserva->id,
        ];
    }
}