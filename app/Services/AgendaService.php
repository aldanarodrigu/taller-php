<?php

namespace App\Services;

use App\Repositories\DisponibilidadRepository;
use App\Repositories\ReservaRepository;
use App\Repositories\ExcepcionRepository;
use App\Repositories\ServicioRepository;
use Exception;

class AgendaService
{
    public function __construct(
        private DisponibilidadRepository $disponibilidadRepository,
        private ReservaRepository $reservaRepository,
        private ExcepcionRepository $excepcionRepository,
        private ServicioRepository $servicioRepository
    ) {}

    public function obtenerAgendaProfesional(int $profesionalId, string $fecha, int $servicioId): array
    {
        $servicio = $this->servicioRepository->findById($servicioId);

        if (!$servicio) {
            throw new Exception('El servicio no fue encontrado', 404);
        }

        if ($servicio->profesional_id !== $profesionalId) {
            throw new Exception('El servicio no pertenece al profesional indicado', 409);
        }

        $fechaObj = new \DateTime($fecha);

        $dias = [
            'Monday' => 'lunes',
            'Tuesday' => 'martes',
            'Wednesday' => 'miércoles',
            'Thursday' => 'jueves',
            'Friday' => 'viernes',
            'Saturday' => 'sabado',
            'Sunday' => 'domingo',
        ];

        $diaSemana = $dias[$fechaObj->format('l')];

        $disponibilidades = $this->disponibilidadRepository
            ->findByProfesionalAndDia($profesionalId, $diaSemana);

        $excepciones = $this->excepcionRepository
            ->findByProfesionalAndFecha($profesionalId, $fecha);

        $reservas = $this->reservaRepository
            ->findByServicioFechaAndEstadosActivos($servicioId, $fecha);
        
        $horariosDisponibles = [];

        foreach ($disponibilidades as $disponibilidad) {
            $buffer = $disponibilidad->buffer ?? 0;

            $horaActual = new \DateTime($disponibilidad->hora_inicio);
            $horaFinDisponibilidad = new \DateTime($disponibilidad->hora_fin);

            while ($horaActual < $horaFinDisponibilidad) {
                $inicioTexto = $horaActual->format('H:i');

                $finTurno = clone $horaActual;
                $finTurno->modify('+' . $servicio->duracion_minutos . ' minutes');
                $finTexto = $finTurno->format('H:i');

                $finTurnoConBuffer = clone $finTurno;
                $finTurnoConBuffer->modify('+' . $buffer . ' minutes');
                $finConBufferTexto = $finTurnoConBuffer->format('H:i');
                
                $estaLibre = true;

                // 1. Validar pausa normal
                if (
                    $disponibilidad->hora_inicio_pausa !== null &&
                    $disponibilidad->hora_fin_pausa !== null
                ) {
                    
                    $inicioPausaTexto = (new \DateTime($disponibilidad->hora_inicio_pausa))->format('H:i');
                    $finPausaTexto = (new \DateTime($disponibilidad->hora_fin_pausa))->format('H:i');

                    $chocaConPausa =
                        $inicioTexto < $disponibilidad->hora_fin_pausa &&
                        $finTexto > $disponibilidad->hora_inicio_pausa;

                    if ($chocaConPausa) {
                        $estaLibre = false;
                    }
                }

                // 2. Validar excepciones
                foreach ($excepciones as $excepcion) {
                    if (in_array($excepcion->tipo, ['bloqueo', 'feriado', 'licencia', 'pausa'])) {
                        if ($excepcion->hora_inicio === null || $excepcion->hora_fin === null) {
                            $estaLibre = false;
                            break;
                        }

                        $chocaConExcepcion =
                            $inicioTexto < $excepcion->hora_fin &&
                            $finTexto > $excepcion->hora_inicio;

                        if ($chocaConExcepcion) {
                            $estaLibre = false;
                            break;
                        }
                    }
                }

                // 3. Validar reservas existentes + buffer
                foreach ($reservas as $reserva) {
                    $finReservaConBuffer = new \DateTime($reserva->hora_fin);
                    $finReservaConBuffer->modify('+' . $buffer . ' minutes');
                    $finReservaConBufferTexto = $finReservaConBuffer->format('H:i');

                    $inicioReservaTexto = (new \DateTime($reserva->hora_inicio))->format('H:i');
                    
                    $chocaConReserva =
                        $inicioTexto < $finReservaConBufferTexto &&
                        $finTexto > $reserva->hora_inicio;

                    if ($chocaConReserva) {
                        $estaLibre = false;
                        break;
                    }
                }
                
                logger()->info('Agenda debug', [
                    'hora_actual' => $horaActual->format('H:i:s'),
                    'hora_fin_disponibilidad' => $horaFinDisponibilidad->format('H:i:s')
                ]);

                // 4. Agregar si entra en disponibilidad y está libre
                if ($finConBufferTexto <= $disponibilidad->hora_fin && $estaLibre) {
                    $horariosDisponibles[] = $inicioTexto;
                }

                $horaActual->modify('+' . ($servicio->duracion_minutos + $buffer) . ' minutes');
                
                logger()->info('Agenda siguiente vuelta', [
                    'hora_actual' => $horaActual->format('H:i:s')
                ]);
                
                
            }
        }
        return [
            'profesional_id' => $profesionalId,
            'servicio_id' => $servicioId,
            'fecha' => $fecha,
            'dia_semana' => $diaSemana,
            'duracion_minutos' => $servicio->duracion_minutos,
            'horarios_disponibles' => $horariosDisponibles
        ];
    }
}