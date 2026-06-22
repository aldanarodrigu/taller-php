<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

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
        return ['database', 'mail', 'broadcast'];
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

    public function toMail(object $notifiable): MailMessage{
        return (new MailMessage) 
            ->subject('Nueva reserva creada') 
            ->greeting('Hola ' . $notifiable->nombre . '!') 
            ->line('Tu reserva fue creada correctamente.') 
            ->line('Fecha: ' . $this->reserva->fecha) 
            ->line('Hora: ' . $this->reserva->hora_inicio) 
            ->line('Estado: ' . $this->reserva->estado) 
            ->action('Ver mis reservas', url('/reservas')) 
            ->line('Gracias por usar nuestro sistema.');
    }

}