<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActividadService;

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
}