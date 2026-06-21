<?php

namespace App\Services;

use App\Models\Pago;
use App\Models\Reserva;
use App\Services\ActividadService;
use App\Repositories\PagoRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Events\AgendaActualizada;
use App\Jobs\NotificarCambioReserva;

class PagoService
{
    public function __construct(
        private PagoRepository $pagoRepository,
        private ActividadService $actividadService,
    ) {}

    public function crear(Request $request): Pago
    {
        return DB::transaction(function () use ($request) {
            $usuario = $request->user();

            if (!$usuario || !$usuario->cliente) {
                throw new Exception(
                    'Solo un cliente puede realizar pagos',
                    403
                );
            }

            $reserva = Reserva::with('servicio')
                ->lockForUpdate()
                ->find($request->reserva_id);

            if (!$reserva) {
                throw new Exception(
                    'Reserva no encontrada',
                    404
                );
            }

            if (
                (int) $reserva->cliente_id !==
                (int) $usuario->cliente->id
            ) {
                throw new Exception(
                    'No podés pagar una reserva que no es tuya',
                    403
                );
            }

            if (!in_array(
                $reserva->estado,
                ['pendiente', 'confirmada'],
                true
            )) {
                throw new Exception(
                    'La reserva no está en un estado pagable',
                    422
                );
            }

            $pagoExistente = Pago::where(
                'reserva_id',
                $reserva->id
            )
                ->where('usuario_id', $usuario->id)
                ->whereIn('estado', ['pendiente', 'aprobado'])
                ->latest('id')
                ->first();

            if ($reserva->estado === 'pendiente') {
                if ($pagoExistente) {
                    if ($pagoExistente->estado === 'aprobado') {
                        throw new Exception(
                            'La reserva ya tiene un pago aprobado',
                            409
                        );
                    }

                    return $pagoExistente->load('reserva');
                }

                $pago = $this->pagoRepository->create([
                    'reserva_id' => $reserva->id,
                    'usuario_id' => $usuario->id,
                    'monto'      => $request->monto,
                    'metodo'     => 'simulado',
                    'estado'     => 'pendiente',
                    'fecha_pago' => null,
                    'hora_pago'  => null,
                ]);

                $this->actividadService->registrar(
                    $usuario->id,
                    'PAGO',
                    'Inició el pago de la reserva #' .
                        $reserva->id
                );

                return $pago->load('reserva');
            }

            if ($pagoExistente) {
                if ($pagoExistente->estado === 'aprobado') {
                    throw new Exception(
                        'La reserva ya está pagada',
                        409
                    );
                }

                $pagoExistente->update([
                    'estado'     => 'aprobado',
                    'fecha_pago' => now()->toDateString(),
                    'hora_pago'  => now()->toTimeString(),
                ]);

                $pago = $pagoExistente;
            } else {
                $pago = $this->pagoRepository->create([
                    'reserva_id' => $reserva->id,
                    'usuario_id' => $usuario->id,
                    'monto'      => $request->monto,
                    'metodo'     => 'simulado',
                    'estado'     => 'aprobado',
                    'fecha_pago' => now()->toDateString(),
                    'hora_pago'  => now()->toTimeString(),
                ]);
            }

            $reserva->update([
                'pago_id' => $pago->id,
                'estado'  => 'pagada',
            ]);

            $reserva->refresh();

            $this->actividadService->registrar(
                $usuario->id,
                'PAGO',
                'Realizó el pago de la reserva #' .
                    $reserva->id
            );

            event(
                new AgendaActualizada(
                    $reserva,
                    'pagada'
                )
            );

            NotificarCambioReserva::dispatch(
                $reserva->id,
                'pagada'
            );

            return $pago->load('reserva');
        });
    }

    public function obtener(int $id): Pago
    {
        $pago = $this->pagoRepository->findById($id);

        if (!$pago) {
            throw new Exception('Pago no encontrado', 404);
        }

        return $pago->load('reserva');
    }

    public function listarDelUsuario(Request $request)
    {
        return $this->pagoRepository->findByUsuario($request->user()->id);
    }

    public function reintentar(
        Request $request,
        int $id
    ): Pago {
        return DB::transaction(function () use ($request, $id) {
            $usuario = $request->user();

            if (!$usuario || !$usuario->cliente) {
                throw new Exception(
                    'Solo un cliente puede reintentar sus pagos',
                    403
                );
            }

            $pago = Pago::with('reserva')
                ->lockForUpdate()
                ->find($id);

            if (!$pago) {
                throw new Exception(
                    'Pago no encontrado',
                    404
                );
            }

            if ((int) $pago->usuario_id !== (int) $usuario->id) {
                throw new Exception(
                    'No podés reintentar un pago que no es tuyo',
                    403
                );
            }

            if ($pago->estado !== 'rechazado') {
                throw new Exception(
                    'Solo se pueden reintentar pagos rechazados',
                    422
                );
            }

            $reserva = $pago->reserva;

            if (!$reserva) {
                throw new Exception(
                    'La reserva asociada no fue encontrada',
                    404
                );
            }

            if (!in_array(
                $reserva->estado,
                ['pendiente', 'confirmada'],
                true
            )) {
                throw new Exception(
                    'La reserva ya no se encuentra en un estado válido para pagar',
                    409
                );
            }

            $pago->update([
                'estado' => 'pendiente',
                'fecha_pago' => null,
                'hora_pago' => null,
            ]);

            return $pago->fresh()->load('reserva');
        });
    }
    
    public function rechazar(
        Request $request,
        int $id
    ): Pago {
        return DB::transaction(function () use ($request, $id) {
            $usuario = $request->user();

            if (!$usuario || !$usuario->cliente) {
                throw new Exception(
                    'Solo un cliente puede gestionar sus pagos',
                    403
                );
            }

            $pago = Pago::with('reserva')
                ->lockForUpdate()
                ->find($id);

            if (!$pago) {
                throw new Exception(
                    'Pago no encontrado',
                    404
                );
            }

            if ((int) $pago->usuario_id !== (int) $usuario->id) {
                throw new Exception(
                    'No podés rechazar un pago que no es tuyo',
                    403
                );
            }

            if ($pago->estado !== 'pendiente') {
                throw new Exception(
                    'Solo se pueden rechazar pagos pendientes',
                    422
                );
            }

            $reserva = $pago->reserva;

            if (!$reserva) {
                throw new Exception(
                    'La reserva asociada no fue encontrada',
                    404
                );
            }

            $pago->update([
                'estado' => 'rechazado',
                'fecha_pago' => null,
                'hora_pago' => null,
            ]);

            return $pago->fresh()->load('reserva');
        });
    }
}