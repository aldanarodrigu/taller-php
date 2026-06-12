<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

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
        return ['database', 'mail'];
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

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reserva cancelada')
            ->greeting('Hola ' . $notifiable->nombre . '!')
            ->line('Te informamos que una reserva fue cancelada.')
            ->line('Fecha: ' . $this->reserva->fecha)
            ->line('Hora: ' . $this->reserva->hora_inicio)
            ->action('Ver reservas', url('/reservas'))
            ->line('Si tienes dudas, comunícate con nosotros.');
    }

}