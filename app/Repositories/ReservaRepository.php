<?php

namespace App\Repositories;

use App\Models\Reserva;
use Illuminate\Support\Facades\DB;

class ReservaRepository {
    
    public function create(array $data): Reserva
    {
        return Reserva::create($data);
    }
    
    public function findById(int $id){
        return Reserva::where('id', $id)->first();
    }
    
    public function findAll(){
        return Reserva::all();
    }

    public function findByClienteId(int $clienteId)
    {
        return Reserva::where('cliente_id', $clienteId)->get();
    }

    public function findByProfesionalId(int $profesionalId)
    {
        return Reserva::whereHas('servicio', function ($q) use ($profesionalId) {
            $q->where('profesional_id', $profesionalId);
        })->get();
    }
    
    public function findByServicioAndFecha(int $servicioId, string $fecha){
        return Reserva::where('servicio_id', $servicioId)
            ->where('fecha', $fecha)
            ->get();
    }
    
    public function findByServicioFechaAndEstadosActivos(int $servicioId, string $fecha)
    {
        return Reserva::where('servicio_id', $servicioId)
            ->where('fecha', $fecha)
            ->whereIn('estado', ['pendiente', 'confirmada', 'pagada', 'en_curso'])
            ->get();
    }
    
    public function update(Reserva $reserva, array $data): Reserva
    {
        $reserva->update($data);
        return $reserva;
    }
    
    public function findByServicioFechaAndEstadosActivosForUpdate(
        int $servicioId,
        string $fecha
    ){
        return Reserva::where('servicio_id', $servicioId)
            ->where('fecha', $fecha)
            ->whereIn('estado', ['pendiente', 'confirmada', 'pagada', 'en_curso'])
            ->lockForUpdate()
            ->get();
    }

    public function findByProfesionalFechaAndEstadosActivosForUpdate(
        int $profesionalId,
        string $fecha
    ){
        return Reserva::whereHas('servicio', fn($q) => $q->where('profesional_id', $profesionalId))
            ->where('fecha', $fecha)
            ->whereIn('estado', ['pendiente', 'confirmada', 'pagada', 'en_curso'])
            ->lockForUpdate()
            ->get();
    }
    
    public function findAfectadasPorExcepcion(int $profesionalId,string $fecha,?string $horaInicio,?string $horaFin) {
        return Reserva::query()
            ->whereHas('servicio', function ($query) use ($profesionalId) {
                $query->where('profesional_id', $profesionalId);
            })
            ->where('fecha', $fecha)
            ->whereIn('estado', ['pendiente', 'confirmada', 'pagada'])
            ->when(
                $horaInicio !== null && $horaFin !== null,
                function ($query) use ($horaInicio, $horaFin) {
                    $query
                        ->where('hora_inicio', '<', $horaFin)
                        ->where('hora_fin', '>', $horaInicio);
                }
            )
            ->lockForUpdate()
            ->get();
    }
    
    public function limpiarReprogramacion(Reserva $reserva): Reserva
    {
        Reserva::query()
            ->where('id', $reserva->id)
            ->update([
                'requiere_reprogramacion' => DB::raw('false'),
                'excepcion_id' => null,
                'motivo_reprogramacion' => null,
            ]);

        return $reserva->refresh();
    }
    
    public function findByProfesionalFechaAndEstadosActivos(int $profesionalId,string $fecha) {
        return Reserva::query()
            ->whereHas('servicio', function ($query) use ($profesionalId) {
                $query->where('profesional_id', $profesionalId);
            })
            ->whereDate('fecha', $fecha)
            ->whereIn('estado', [
                'pendiente',
                'confirmada',
                'pagada',
            ])
            ->orderBy('hora_inicio')
            ->get();
    }
}
