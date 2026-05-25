<?php

namespace App\Http\Controllers;

use App\Services\ProfesionalService;
use Illuminate\Http\Request;

class ProfesionalController extends Controller{
    
    private ProfesionalService $profesionalService;

    public function __construct(ProfesionalService $profesionalService){
        $this->profesionalService = $profesionalService;
    }

}
