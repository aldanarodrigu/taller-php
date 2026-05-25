<?php

namespace App\Http\Controllers;

use App\Services\ClienteService;
use Illuminate\Http\Request;

class ClienteController extends Controller{
    
    private ClienteService $clienteService;

    public function __construct(ClienteService $clienteService){
        $this->clienteService = $clienteService;
    }

}
