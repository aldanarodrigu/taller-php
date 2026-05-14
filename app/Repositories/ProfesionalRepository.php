<?php

namespace App\Repositories;

use App\Models\Profesional;

class ProfesionalRepository{
    public function create(array $data){
        return Profesional::create($data);
    }
}