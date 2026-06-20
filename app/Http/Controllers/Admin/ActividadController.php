<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActividadService;
use App\Models\Profesional;
use App\Models\Reserva;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class ActividadController extends Controller
{
    private ActividadService $actividadService;

    public function __construct(ActividadService $actividadService)
    {
        $this->actividadService = $actividadService;
    }

    public function index()
    {
        return response()->json(
            $this->actividadService->obtenerUltimas()
        );
    }

    public function metricas(): JsonResponse
    {
        return response()->json([
            'cant_usuarios'      => User::count(),
            'cant_clientes'      => User::where('role', 'cliente')->count(),
            'cant_profesionales' => Profesional::count(),
            'cant_reservas'      => Reserva::count(),
        ]);
    }
}