<?php

namespace App\Http\Controllers;

use App\Services\ProfesionalService;
use Illuminate\Http\Request;

class ProfesionalController extends Controller{
    
    private ProfesionalService $profesionalService;

    public function __construct(ProfesionalService $profesionalService){
        $this->profesionalService = $profesionalService;
    }

    public function editarProfesional(Request $request)
    {
        $request->validate([
            'descripcion' => 'nullable|string',
            'profesion' => 'nullable|string|max:255',
            'foto' => 'nullable|image|max:2048',
            'modalidad_atencion' => 'nullable|string'
        ]);

        $user = $request->user();

        $profesional = $this->profesionalService
            ->editarProfesional($user, $request);

        return response()->json([
            'message' => 'Perfil actualizado correctamente',
            'profesional' => $profesional
        ]);
    }

}
