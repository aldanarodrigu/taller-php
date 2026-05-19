<?php

namespace App\Repositories;

use App\Models\Disponibilidad;

class DisponibilidadRepository {
    
    public function create(array $data){
        return Disponibilidad::create($data);
    }

    public function findById(int $id){
        return Disponibilidad::where('id', $id)->first();
    }
    
    public function findByProfesionalId(int $profesionalId)
    {
        return Disponibilidad::where('profesional_id', $profesionalId)->get();
    }

    public function findAll(){
        return Disponibilidad::all();
    }
    
    public function findByProfesionalAndDia(int $profesionalId, string $diaSemana){
        return Disponibilidad::where('profesional_id', $profesionalId)
            ->where('dia_semana', $diaSemana)
            ->get();
    }

}
