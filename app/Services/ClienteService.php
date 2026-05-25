<?php

namespace App\Services;

use App\Repositories\ClienteRepository;
use App\Models\Cliente;
use Exception;

class ClienteService{

    private ClienteRepository $clienteRepository;

    public function __construct(ClienteRepository $clienteRepository){
        $this->clienteRepository = $clienteRepository;
    }

}