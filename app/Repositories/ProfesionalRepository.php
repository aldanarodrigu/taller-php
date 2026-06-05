<?php

namespace App\Repositories;

use App\Models\Profesional;

class ProfesionalRepository{
    public function create(array $data){
        return Profesional::create($data);
    }

    public function findByUserId(int $userId): Profesional{
        return Profesional::where('user_id', $userId)
            ->firstOrFail();
    }

}