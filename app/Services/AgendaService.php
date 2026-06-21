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

    public function obtenerAgendaProfesional(
        int $profesionalId,
        string $fecha,
        int $servicioId
    ): array {

        $servicio = $this->servicioRepository->findById($servicioId);

        if (!$servicio) {
            throw new Exception(
                'El servicio no fue encontrado',
                404
            );
        }

        if ((int) $servicio->profesional_id !== $profesionalId) {
            throw new Exception(
                'El servicio no pertenece al profesional indicado',
                409
            );
        }

        $duracion = (int) $servicio->duracion_minutos;

        if ($duracion <= 0) {
            throw new Exception(
                'El servicio no tiene una duración válida',
                500
            );
        }

        $fechaObj = \DateTime::createFromFormat(
            '!Y-m-d',
            $fecha
        );

        $erroresFecha = \DateTime::getLastErrors();

        $fechaInvalida =
            $fechaObj === false ||
            (
                is_array($erroresFecha) &&
                (
                    $erroresFecha['warning_count'] > 0 ||
                    $erroresFecha['error_count'] > 0
                )
            ) ||
            $fechaObj->format('Y-m-d') !== $fecha;

        if ($fechaInvalida) {
            throw new Exception(
                'La fecha indicada no es válida',
                422
            );
        }
        
        $hoy = new \DateTime('today');

        if ($fechaObj < $hoy) {
            throw new Exception(
                'No se puede consultar la agenda de una fecha anterior',
                422
            );
        }

        $ahora = new \DateTime();
        $esHoy =
            $fechaObj->format('Y-m-d') ===
            $ahora->format('Y-m-d');

        $dias = [
            'Monday' => 'lunes',
            'Tuesday' => 'martes',
            'Wednesday' => 'miércoles',
            'Thursday' => 'jueves',
            'Friday' => 'viernes',
            'Saturday' => 'sabado',
            'Sunday' => 'domingo',
        ];

        $diaIngles = $fechaObj->format('l');
        $diaSemana = $dias[$diaIngles] ?? null;

        if (!$diaSemana) {
            throw new Exception(
                'No se pudo determinar el día de la semana',
                500
            );
        }

        $disponibilidades = $this->disponibilidadRepository
            ->findByProfesionalAndDia(
                $profesionalId,
                $diaSemana
            );

        $excepciones = $this->excepcionRepository
            ->findByProfesionalAndFecha(
                $profesionalId,
                $fecha
            );

        $reservas = $this->reservaRepository
            ->findByProfesionalFechaAndEstadosActivos(
                $profesionalId,
                $fecha
            );

        $horariosDisponibles = [];

        foreach ($disponibilidades as $disponibilidad) {
            $buffer = (int) ($disponibilidad->buffer ?? 0);

            if ($buffer < 0) {
                $buffer = 0;
            }

            $avanceMinutos = $duracion + $buffer;

            if ($avanceMinutos <= 0) {
                throw new Exception(
                    'La duración y el buffer producen un avance inválido',
                    500
                );
            }

            $horaActual = new \DateTime(
                $disponibilidad->hora_inicio
            );

            $horaFinDisponibilidad = new \DateTime(
                $disponibilidad->hora_fin
            );

            $inicioPausa =
                $disponibilidad->hora_inicio_pausa !== null
                    ? new \DateTime(
                        $disponibilidad->hora_inicio_pausa
                    )
                    : null;

            $finPausa =
                $disponibilidad->hora_fin_pausa !== null
                    ? new \DateTime(
                        $disponibilidad->hora_fin_pausa
                    )
                    : null;

            $iteraciones = 0;

            while ($horaActual < $horaFinDisponibilidad) {
                $iteraciones++;

                if ($iteraciones > 200) {
                    throw new Exception(
                        'Se detectó un ciclo infinito al generar los horarios',
                        500
                    );
                }

                $finTurno = clone $horaActual;
                $finTurno->modify(
                    '+' . $duracion . ' minutes'
                );

                $finTurnoConBuffer = clone $finTurno;
                $finTurnoConBuffer->modify(
                    '+' . $buffer . ' minutes'
                );
                
                if ($finTurnoConBuffer > $horaFinDisponibilidad) {
                    break;
                }
                
                if ($esHoy) {
                    $fechaHoraTurno = new \DateTime(
                        $fecha . ' ' . $horaActual->format('H:i:s')
                    );

                    if ($fechaHoraTurno <= $ahora) {
                        $horaActual = clone $finTurnoConBuffer;
                        continue;
                    }
                }

                $estaLibre = true;
                $proximoInicio = null;
                $motivoBloqueo = null;

                if (
                    $inicioPausa !== null &&
                    $finPausa !== null
                ) {
                    $chocaConPausa =
                        $horaActual < $finPausa &&
                        $finTurno > $inicioPausa;

                    if ($chocaConPausa) {
                        $estaLibre = false;
                        $proximoInicio = clone $finPausa;
                        $motivoBloqueo = 'pausa habitual';
                    }
                }

                foreach ($excepciones as $excepcion) {
                    if (!in_array(
                        $excepcion->tipo,
                        [
                            'bloqueo',
                            'feriado',
                            'licencia',
                            'pausa',
                        ],
                        true
                    )) {
                        continue;
                    }

                    if (
                        $excepcion->hora_inicio === null ||
                        $excepcion->hora_fin === null
                    ) {
                        $estaLibre = false;
                        $proximoInicio =
                            clone $horaFinDisponibilidad;

                        $motivoBloqueo =
                            'excepción de día completo';

                        break;
                    }

                    $inicioExcepcion = new \DateTime(
                        $excepcion->hora_inicio
                    );

                    $finExcepcion = new \DateTime(
                        $excepcion->hora_fin
                    );

                    $chocaConExcepcion =
                        $horaActual < $finExcepcion &&
                        $finTurno > $inicioExcepcion;

                    if ($chocaConExcepcion) {
                        $estaLibre = false;
                        $motivoBloqueo =
                            'excepción ' . $excepcion->tipo;

                        if (
                            $proximoInicio === null ||
                            $finExcepcion > $proximoInicio
                        ) {
                            $proximoInicio =
                                clone $finExcepcion;
                        }
                    }
                }


                foreach ($reservas as $reserva) {
                    $inicioReserva = new \DateTime(
                        $reserva->hora_inicio
                    );

                    $finReservaConBuffer = new \DateTime(
                        $reserva->hora_fin
                    );

                    $finReservaConBuffer->modify(
                        '+' . $buffer . ' minutes'
                    );

                    $chocaConReserva =
                        $horaActual < $finReservaConBuffer &&
                        $finTurnoConBuffer > $inicioReserva;

                    if ($chocaConReserva) {
                        $estaLibre = false;
                        $motivoBloqueo =
                            'reserva existente #' . $reserva->id;

                        if (
                            $proximoInicio === null ||
                            $finReservaConBuffer > $proximoInicio
                        ) {
                            $proximoInicio =
                                clone $finReservaConBuffer;
                        }
                    }
                }

                if ($estaLibre) {
                    $horario = $horaActual->format('H:i');

                    $horariosDisponibles[] = $horario;

                    $horaActual = clone $finTurnoConBuffer;

                    continue;
                }

                if (
                    $proximoInicio !== null &&
                    $proximoInicio > $horaActual
                ) {
                    $horaActual = clone $proximoInicio;

                    continue;
                }

                $horaActual->modify('+1 minute');
            }
        }

        $horariosDisponibles = array_values(
            array_unique($horariosDisponibles)
        );

        sort($horariosDisponibles);

        return [
            'profesional_id' => $profesionalId,
            'servicio_id' => $servicioId,
            'fecha' => $fecha,
            'dia_semana' => $diaSemana,
            'duracion_minutos' => $duracion,
            'horarios_disponibles' => $horariosDisponibles,
        ];
    }
}