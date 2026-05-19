<?php

namespace App\Services;
use App\Repositories\ReservaRepository;
use App\Repositories\ServicioRepository;
use App\Repositories\DisponibilidadRepository;
use App\Models\Reserva;
use Exception;
use Illuminate\Http\Request;

class ReservaService {
    
    public function __construct(
        private ReservaRepository $reservaRepository,
        private ServicioRepository $servicioRepository,
        private DisponibilidadRepository $disponibilidadRepository
    ) {}
    
    public function crear(Request $request): Reserva{
        $cliente = $request->user()->cliente;

        if (!$cliente) {
            throw new Exception('El usuario no tiene perfil de cliente', 403);
        }
        
        $servicio = $this->servicioRepository->findById($request->servicio_id);
        
        if (!$servicio) {
            throw new Exception('El servicio no fue encontrado', 404);
        }
        
        $horaInicio = new \DateTime($request->hora_inicio);
        $horaFin = clone $horaInicio;
        $horaFin->modify('+' . $servicio->duracion_minutos . ' minutes');

        $horaFinTexto = $horaFin->format('H:i');
        
        $disponibilidades = $this->disponibilidadRepository->findByProfesionalAndDia($servicio->profesional_id, $request->dia_semana);
        
        $hayDisponibilidad = false;

        foreach ($disponibilidades as $disponibilidad) {
            if (
                $request->hora_inicio >= $disponibilidad->hora_inicio &&
                $horaFinTexto <= $disponibilidad->hora_fin
            ) {
                $hayDisponibilidad = true;
                break;
            }
        }

        if (!$hayDisponibilidad) {
            throw new Exception('El horario solicitado no está dentro de la disponibilidad del profesional', 409);
        }
        
        $reservasDelDia = $this->reservaRepository->findByServicioFechaAndEstadosActivos($servicio->id, $request->fecha);

        foreach ($reservasDelDia as $reserva) {
            $seSuperpone = $request->hora_inicio < $reserva->hora_fin && $horaFinTexto > $reserva->hora_inicio;

            if ($seSuperpone) {
                throw new Exception('Ya existe una reserva en ese horario', 409);
            }
        }
        
        return $this->reservaRepository->create([
            'cliente_id'   => $cliente->id,
            'servicio_id'  => $servicio->id,
            'pago_id'      => null,
            'fecha'        => $request->fecha,
            'hora_inicio'  => $request->hora_inicio,
            'hora_fin'     => $horaFinTexto,
            'estado'       => 'pendiente'
        ]);
    }
    
    public function listar()
    {
        return $this->reservaRepository->findAll();
    }
    
    
    public function obtener(int $id): Reserva
    {
        $reserva = $this->reservaRepository->findById($id);

        if (!$reserva) {
            throw new Exception('La reserva no fue encontrada', 404);
        }

        return $reserva;
    }
}
