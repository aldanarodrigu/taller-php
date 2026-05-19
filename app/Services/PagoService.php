<?php

namespace App\Services;

use App\Repositories\PagoRepository;
use App\Models\Pago;
use Exception;
use Illuminate\Http\Request;

class PagoService 
{
    
    public function __construct(
        private PagoRepository $pagoRepository
    ) {}

    public function crear(Request $request): Pago
    {
        return $this->pagoRepository->create([
            'monto'           => $request->monto,
            'metodo' => 'simulado',
            'estado' => 'aprobado',
            'fecha_pago' => date('Y-m-d'),
            'hora_pago' => date('H:i:s')
        ]);
    }
    
    public function listar()
    {
        return $this->pagoRepository->findAll();
    }

    public function obtener(int $id): Pago
    {
        $pago = $this->pagoRepository->findById($id);

        if (!$pago) {
            throw new Exception('Pago no encontrado', 404);
        }

        return $pago;
    }
}
