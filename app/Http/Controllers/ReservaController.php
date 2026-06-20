<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Services\ReservaService;
use Exception;
use Illuminate\Http\Request;

class ReservaController extends Controller
{
    public function __construct(
        private ReservaService $reservaService
    ) {}

    // POST /reservas
    public function store(Request $request)
    {
        $request->validate([
            'servicio_id' => 'required|integer',
            'fecha' => 'required|date',
            'hora_inicio' => 'required|date_format:H:i'
        ]);

        try {
            $reserva = $this->reservaService->crear($request);
            return response()->json($reserva, 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
    
    // GET /reservas
    public function index(Request $request)
    {
        try {
            $reservas = $this->reservaService->listar($request);
            return response()->json($reservas, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
    
    // GET /reservas/{id}
    public function show(int $id)
    {
        try {
            $reserva = $this->reservaService->obtener($id);
            return response()->json($reserva, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
    
    // PATCH /reservas/{id}/cancelar
    public function cancelar(Request $request, int $id)
    {
        try {
            $reserva = $this->reservaService->cancelar($request, $id);
            return response()->json($reserva, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
    
    public function confirmar(Request $request, int $id)
    {
        try {
            $reserva = $this->reservaService->confirmar($request, $id);
            return response()->json($reserva, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
    
    public function iniciar(Request $request, int $id)
    {
        return response()->json(
            $this->reservaService->iniciar($request, $id)
        );
    }

    public function finalizar(Request $request, int $id)
    {
        return response()->json(
            $this->reservaService->finalizar($request, $id)
        );
    }

    public function noAsistida(Request $request, int $id)
    {
        return response()->json(
            $this->reservaService->noAsistida($request, $id)
        );
    }
    
    // PATCH /reservas/{id}/reprogramar
    public function reprogramar(Request $request, int $id)
    {
        $request->validate([
            'fecha' => 'required|date',
            'hora_inicio' => 'required|date_format:H:i',
        ]);

        try {
            $reserva = $this->reservaService->reprogramar($request, $id);

            return response()->json([
                'mensaje' => 'Reserva reprogramada correctamente',
                'reserva' => $reserva,
            ], 200);
        } catch (Exception $e) {
            return response()->json(
                ['error' => $e->getMessage()],
                $e->getCode() ?: 400
            );
        }
    }

    // GET /reservas/mis-clientes
    public function misClientes(Request $request)
    {
        $profesional = $request->user()->profesional;

        if (!$profesional) {
            return response()->json(['error' => 'No tenés perfil de profesional'], 403);
        }

        $clientes = Cliente::whereHas('reservas', function ($q) use ($profesional) {
            $q->whereHas('servicio', fn($s) => $s->where('profesional_id', $profesional->id));
        })
        ->with([
            'user',
            'reservas' => function ($q) use ($profesional) {
                $q->whereHas('servicio', fn($s) => $s->where('profesional_id', $profesional->id))
                  ->with('servicio:id,nombre')
                  ->orderBy('fecha', 'desc')
                  ->orderBy('id', 'desc');
            }
        ])
        ->get()
        ->map(fn($c) => [
            'id'       => $c->id,
            'nombre'   => $c->user->nombre,
            'apellido' => $c->user->apellido,
            'email'    => $c->user->email,
            'reservas' => $c->reservas->map(fn($r) => [
                'id'             => $r->id,
                'fecha'          => $r->fecha,
                'hora_inicio'    => $r->hora_inicio,
                'estado'         => $r->estado,
                'servicio_nombre'=> $r->servicio?->nombre,
            ]),
        ]);

        return response()->json($clientes);
    }
}