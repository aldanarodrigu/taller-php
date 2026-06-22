<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Models\Reserva;

class RecordatorioTurnoNotification extends Notification
{
    use Queueable;

    private Reserva $reserva;

    public function __construct(Reserva $reserva)
    {
        $this->reserva = $reserva;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tipo' => 'recordatorio_turno',
            'titulo' => 'Recordatorio de turno',
            'mensaje' => 'Tienes un turno próximo',
            'reserva_id' => $this->reserva->id,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Recordatorio de turno')
            ->greeting('Hola ' . $notifiable->nombre . '!')
            ->line('Te recordamos que tienes un turno próximo.')
            ->line('Fecha: ' . $this->reserva->fecha)
            ->line('Hora: ' . $this->reserva->hora_inicio)
            ->line('Estado: ' . $this->reserva->estado)
            ->action('Ver mis reservas', url('/reservas'))
            ->line('Te esperamos.');
    }

}
