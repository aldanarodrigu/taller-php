<?php

namespace App\Repositories;

use App\Models\Pago;

class PagoRepository 
{
    public function create(array $data): Pago
    {
        return Pago::create($data);
    }
    
    public function findById(int $id){
        return Pago::where('id', $id)->first();
    }
    
    public function findAll(){
        return Pago::all();
    }
}
