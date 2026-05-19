<?php

namespace App\Repositories;

use App\Models\Reserva;

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
}
