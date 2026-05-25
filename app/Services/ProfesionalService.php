<?php

namespace App\Services;

use App\Repositories\ProfesionalRepository;
use App\Models\Profesional;
use Exception;

class ProfesionalService{

    private ProfesionalRepository $profesionalRepository;

    public function __construct(ProfesionalRepository $profesionalRepository){
        $this->profesionalRepository = $profesionalRepository;
    }

}