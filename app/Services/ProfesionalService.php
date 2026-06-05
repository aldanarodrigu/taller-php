<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Repositories\ProfesionalRepository;
use App\Models\Profesional;
use Exception;

class ProfesionalService{

    private ProfesionalRepository $profesionalRepository;

    public function __construct(ProfesionalRepository $profesionalRepository){
        $this->profesionalRepository = $profesionalRepository;
    }

    public function editarProfesional(User $user, Request $request)
    {
        $profesional = $this->profesionalRepository
            ->findByUserId($user->id);

        $datos = [
            'descripcion' => $request->descripcion,
            'profesion' => $request->profesion,
        ];

        if ($request->hasFile('foto')) {
            $datos['foto'] = $request->file('foto')
                ->store('profesionales', 'public');
        }

        $profesional->update($datos);

        return $profesional;
    }
}

