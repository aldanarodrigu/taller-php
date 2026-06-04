<?php

namespace App\Services;
use App\Notifications\ReservaCanceladaNotificacion;
use App\Repositories\ReservaRepository;
use App\Repositories\ServicioRepository;
use App\Repositories\DisponibilidadRepository;
use App\Repositories\ExcepcionRepository;
use App\Models\Reserva;
use App\Events\AgendaActualizada;

use App\Notifications\NuevaReservaNotification;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\LockTimeoutException;


class ReservaService {
    
    public function __construct(
        private ReservaRepository $reservaRepository,
        private ServicioRepository $servicioRepository,
        private DisponibilidadRepository $disponibilidadRepository,
        private ExcepcionRepository $excepcionRepository
    ) {}
    
    public function crear(Request $request): Reserva{
        
        $lockKey = 'reserva:servicio_' . $request->servicio_id . ':fecha_' . $request->fecha;

        $lock = Cache::lock($lockKey, 10);
        
        try {
            return $lock->block(5, function () use ($request) {
                return DB::transaction(function () use ($request) {
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

                    $fecha = new \DateTime($request->fecha);

                    $dias = [
                        'Monday' => 'lunes',
                        'Tuesday' => 'martes',
                        'Wednesday' => 'miércoles',
                        'Thursday' => 'jueves',
                        'Friday' => 'viernes',
                        'Saturday' => 'sabado',
                        'Sunday' => 'domingo',
                    ];

                    $diaSemana = $dias[$fecha->format('l')];

                    $disponibilidades = $this->disponibilidadRepository->findByProfesionalAndDia($servicio->profesional_id, $diaSemana);

                    $hayDisponibilidad = false;
                    $disponibilidadUsada = null;

                    foreach ($disponibilidades as $disponibilidad) {
                        $inicioDisponibilidad = (new \DateTime($disponibilidad->hora_inicio))->format('H:i');
                        $finDisponibilidad = (new \DateTime($disponibilidad->hora_fin))->format('H:i');

                        if (
                            $request->hora_inicio >= $inicioDisponibilidad &&
                            $horaFinTexto <= $finDisponibilidad
                        ) {
                            $hayDisponibilidad = true;
                            $disponibilidadUsada = $disponibilidad;
                            break;
                        }
                    }

                    if (!$hayDisponibilidad) {
                        throw new Exception('El horario solicitado no está dentro de la disponibilidad del profesional', 409);
                    }

                    $excepciones = $this->excepcionRepository->findByProfesionalAndFecha($servicio->profesional_id, $request->fecha);

                    foreach ($excepciones as $excepcion) {
                        if (in_array($excepcion->tipo, ['bloqueo', 'feriado', 'licencia', 'pausa'])) {
                            if ($excepcion->hora_inicio === null || $excepcion->hora_fin === null) {
                                throw new Exception('El profesional no está disponible en esa fecha', 409);
                            }

                            $seSuperponeConExcepcion =
                                $request->hora_inicio < $excepcion->hora_fin &&
                                $horaFinTexto > $excepcion->hora_inicio;

                            if ($seSuperponeConExcepcion){
                                throw new Exception('El horario solicitado coincide con una excepción del profesional', 409);
                            }
                        }
                    }

                    $reservasDelDia = $this->reservaRepository->findByServicioFechaAndEstadosActivosForUpdate($servicio->id, $request->fecha);

                    $buffer = $disponibilidadUsada?->buffer ?? 0;

                    $horaFinConBuffer = clone $horaFin;
                    $horaFinConBuffer->modify('+' . $buffer . ' minutes');
                    $horaFinConBufferTexto = $horaFinConBuffer->format('H:i');

                    $finDisponibilidadUsada = (new \DateTime($disponibilidadUsada->hora_fin))->format('H:i');
                    
                    if ($horaFinConBufferTexto > $finDisponibilidadUsada) {
                        throw new Exception('La reserva más el buffer excede el horario disponible del profesional', 409);
                    }

                    if (
                        $disponibilidadUsada->hora_inicio_pausa !== null &&
                        $disponibilidadUsada->hora_fin_pausa !== null
                    ) {
                        $seSuperponeConPausa =
                            $request->hora_inicio < $disponibilidadUsada->hora_fin_pausa &&
                            $horaFinTexto > $disponibilidadUsada->hora_inicio_pausa;

                        if ($seSuperponeConPausa) {
                            throw new Exception('El horario solicitado coincide con la pausa del profesional', 409);
                        }
                    }

                    foreach ($reservasDelDia as $reserva) {
                        $inicioReservaExistente = $reserva->hora_inicio;

                        $finReservaExistente = new \DateTime($reserva->hora_fin);
                        $finReservaExistente->modify('+' . $buffer . ' minutes');
                        $finReservaExistenteTexto = $finReservaExistente->format('H:i');

                        $seSuperpone =
                            $request->hora_inicio < $finReservaExistenteTexto &&
                            $horaFinTexto > $inicioReservaExistente;

                        if ($seSuperpone) {
                            throw new Exception('Ya existe una reserva en ese horario o dentro del buffer requerido', 409);
                        }
                    }

                    $reserva = $this->reservaRepository->create([
                        'cliente_id'   => $cliente->id,
                        'servicio_id'  => $servicio->id,
                        'pago_id'      => null,
                        'fecha'        => $request->fecha,
                        'hora_inicio'  => $request->hora_inicio,
                        'hora_fin'     => $horaFinTexto,
                        'estado'       => 'pendiente'
                    ]);

                    event(new AgendaActualizada($reserva, 'creada'));


                    $usuarioProfesional = $servicio->profesional->user;

                    $usuarioProfesional->notify(new NuevaReservaNotification($reserva));

                    return $reserva;
                });
            });
        } catch (LockTimeoutException $e) {
            throw new Exception(
                'La reserva está siendo procesada por otra solicitud, intentá nuevamente',
                409
            );
        }

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
    
    public function cancelar(Request $request, int $id): Reserva
    {
        $cliente = $request->user()->cliente;

        if (!$cliente) {
            throw new Exception('El usuario no tiene perfil de cliente', 403);
        }

        $reserva = $this->obtener($id);

        if ($reserva->cliente_id !== $cliente->id) {
            throw new Exception('No tenés permiso para cancelar esta reserva', 403);
        }

        if ($reserva->estado === 'cancelada') {
            throw new Exception('La reserva ya está cancelada', 409);
        }

        if (in_array($reserva->estado, ['finalizada', 'no_asistida'])) {
            throw new Exception('No se puede cancelar una reserva finalizada o marcada como no asistida', 409);
        }

        $servicio = $this->servicioRepository->findById($reserva->servicio_id);

        if (!$servicio) {
            throw new Exception('El servicio asociado a la reserva no fue encontrado', 404);
        }

        $fechaHoraReserva = new \DateTime($reserva->fecha . ' ' . $reserva->hora_inicio);
        $ahora = new \DateTime();

        $horasMinimas = $servicio->cancelacion_horas_minimas ?? 0;
        $limiteCancelacion = clone $fechaHoraReserva;
        $limiteCancelacion->modify('-' . $horasMinimas . ' hours');

        if ($ahora > $limiteCancelacion) {
            throw new Exception('Ya no es posible cancelar esta reserva por el tiempo mínimo de cancelación', 409);
        }

        $reserva = $this->reservaRepository->update($reserva, [
            'estado' => 'cancelada'
        ]);

        event(new AgendaActualizada($reserva, 'cancelada'));

        $profesionalReserva = $servicio->profesional->user;
        $profesionalReserva->notify(new ReservaCanceladaNotificacion($reserva));

        return $reserva;
    }
}
