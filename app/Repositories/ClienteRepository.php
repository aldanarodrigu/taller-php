<?php

namespace App\Repositories;

use App\Models\Cliente;

class ClienteRepository{
    public function create(array $data){
        return Cliente::create($data);
    }
}