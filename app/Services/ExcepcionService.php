<?php

namespace App\Services;

use App\Models\Excepcion;
use App\Repositories\ExcepcionRepository;
use App\Repositories\ReservaRepository;

use App\Events\AgendaActualizada;
use App\Jobs\NotificarCambioReserva;
use Illuminate\Support\Facades\DB;

use Exception;
use Illuminate\Http\Request;

class ExcepcionService
{
    public function __construct(
        private ExcepcionRepository $excepcionRepository,
        private ReservaRepository $reservaRepository
    ) {}

    public function crear(Request $request): Excepcion
    {
        $profesional = $request->user()->profesional;

        if (!$profesional) {
            throw new Exception('El usuario no tiene perfil de profesional', 403);
        }

        $horaInicio = $request->input('hora_inicio');
        $horaFin = $request->input('hora_fin');

        if (($horaInicio === null && $horaFin !== null) ||
            ($horaInicio !== null && $horaFin === null)) {
            throw new Exception('Se deben completar hora_inicio y hora_fin juntas', 422);
        }

        if ($horaInicio !== null && $horaFin !== null) {
            if ($horaFin <= $horaInicio) {
                throw new Exception('La hora de finalización debe ser mayor a la hora de inicio', 422);
            }
        }

        return DB::transaction(function () use ($request, $profesional, $horaInicio, $horaFin) {
            $excepcion = $this->excepcionRepository->create([
                'profesional_id' => $profesional->id,
                'fecha' => $request->fecha,
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
                'tipo' => $request->tipo,
                'motivo' => $request->motivo,
            ]);

            $reservasAfectadas = $this->reservaRepository->findAfectadasPorExcepcion($profesional->id,$request->fecha,$horaInicio,$horaFin);

            foreach ($reservasAfectadas as $reserva) {
                $reservaActualizada = $this->reservaRepository->update(
                    $reserva,
                    [
                        'requiere_reprogramacion' => DB::raw('true'),
                        'excepcion_id' => $excepcion->id,
                        'motivo_reprogramacion' => $request->motivo
                            ?? 'La reserva fue afectada por una excepción del profesional',
                    ]
                );

                event(
                    new AgendaActualizada(
                        $reservaActualizada,
                        'requiere_reprogramacion'
                    )
                );

                NotificarCambioReserva::dispatch(
                    $reservaActualizada->id,
                    'requiere_reprogramacion'
                );
            }

            return $excepcion;
        });
    }

    public function listar(Request $request)
    {
        $profesional = $request->user()->profesional;

        if (!$profesional) {
            throw new Exception('El usuario no tiene perfil de profesional', 403);
        }

        return $this->excepcionRepository->findByProfesionalId($profesional->id);
    }

    public function obtener(int $id): Excepcion
    {
        $excepcion = $this->excepcionRepository->findById($id);

        if (!$excepcion) {
            throw new Exception('Excepción no encontrada', 404);
        }

        return $excepcion;
    }
}