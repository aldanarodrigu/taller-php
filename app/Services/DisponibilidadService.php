<?php

namespace App\Services;

use App\Repositories\DisponibilidadRepository;
use App\Models\Disponibilidad;
use Exception;
use Illuminate\Http\Request;

class DisponibilidadService {
    
    public function __construct(
        private DisponibilidadRepository $disponibilidadRepository
    ) {}
    
    public function listarPorProfesional(int $profesionalId){
        return $this->disponibilidadRepository->findByProfesionalId($profesionalId);
    }
    
    public function crear(Request $request) : Disponibilidad{
        
        $profesional = $request->user()->profesional;

        if (!$profesional) {
            throw new Exception('El usuario no tiene perfil de profesional', 403);
        }
        
        if ($request->hora_fin <= $request->hora_inicio){
            throw new Exception('La hora de finalizacion debe ser mayor a la hora de inicio', 403);
        }
            
        if ($request->hora_inicio_pausa != null || $request->hora_fin_pausa != null) {
            

            if ($request->hora_inicio_pausa != null && $request->hora_fin_pausa == null){
                throw new Exception('Se deben completar la hora inicio y fin de la pausa', 403);
            }

            if ($request->hora_inicio_pausa == null && $request->hora_fin_pausa != null){
                throw new Exception('Se deben completar la hora inicio y fin de la pausa', 403);
            }

            if ($request->hora_inicio_pausa != null && $request->hora_fin_pausa != null){
                if ($request->hora_inicio_pausa >= $request->hora_fin_pausa){
                    throw new Exception('La hora de finalizacion de la pausa debe ser mayor a la hora de inicio de la pausa', 403);
                }
            }

            if ($request->hora_inicio_pausa < $request->hora_inicio || $request->hora_fin_pausa > $request->hora_fin){
                throw new Exception('La pausa debe estar dentro del horario laboral', 403);
            }
        }
        
        $disponibilidadesDelDia = $this->disponibilidadRepository->findByProfesionalAndDia($profesional->id, $request->dia_semana);

        foreach ($disponibilidadesDelDia as $disponibilidad) {
            $seSuperpone =
                $request->hora_inicio < $disponibilidad->hora_fin &&
                $request->hora_fin > $disponibilidad->hora_inicio;

            if ($seSuperpone) {
                throw new Exception('Ya existe una disponibilidad superpuesta para ese día', 409);
            }
        }
        
        return $this->disponibilidadRepository->create([
        
            'dia_semana'            => $request->dia_semana,
            'hora_inicio'           => $request->hora_inicio,
            'hora_fin'              => $request->hora_fin,
            'buffer'                => $request->buffer ?? 0, 
            'hora_inicio_pausa'     => $request->hora_inicio_pausa,
            'hora_fin_pausa'        => $request->hora_fin_pausa,
            'profesional_id'        => $profesional->id
        ]);
    }
}
