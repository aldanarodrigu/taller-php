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
        $ahora = now();
        $desde = $ahora->copy()->addDay()->startOfMinute();
        $hasta = $desde->copy()->addHour();

        $reservas = Reserva::where('estado', 'confirmada')
            ->whereNull('recordatorio_enviado_at')
            ->whereDate('fecha', $desde->toDateString())
            ->whereTime('hora_inicio', '>=', $desde->format('H:i:00'))
            ->whereTime('hora_inicio', '<', $hasta->format('H:i:00'))
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