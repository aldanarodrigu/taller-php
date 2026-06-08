<?php

namespace App\Console\Commands;

use App\Models\Reserva;
use App\Notifications\RecordatorioTurnoNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class EnviarRecordatoriosTurnos extends Command
{
    protected $signature = 'app:enviar-recordatorios-turnos';

    protected $description = 'Envía recordatorios de turnos próximos';
    public function handle(){
        $objetivo = now()->addDay();

        $reservas = Reserva::where('estado', 'confirmada')
           // ->whereNull('recordatorio_enviado_at')
            ->whereDate('fecha', $objetivo->toDateString())
            ->whereTime('hora_inicio', $objetivo->format('H:i:00'))
            ->get();

        foreach ($reservas as $reserva) {

            $user = $reserva->cliente?->user;

            if (!$user) {
                continue;
            }

            $user->notify(
                new RecordatorioTurnoNotification($reserva)
            );

            $reserva->update([
                'recordatorio_enviado_at' => now()
            ]);
        }

        $this->info('Recordatorios enviados.');
    }
}