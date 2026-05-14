<?php
namespace App\Services;

use App\Repositories\UserRepository;
use App\Repositories\ClienteRepository;
use App\Repositories\ProfesionalRepository;

class AuthService{

    private UserRepository $userRepository;
    private ClienteRepository $clienteRepository;
    private ProfesionalRepository $profesionalRepository;

    public function __construct(UserRepository $userRepository, ClienteRepository $clienteRepository, ProfesionalRepository $profesionalRepository)
    {
        $this->userRepository = $userRepository;
        $this->clienteRepository = $clienteRepository;
        $this->profesionalRepository = $profesionalRepository;
    }

    public function registrar($request){
        $user = $this->userRepository->create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role
        ]);

        if($user->esCliente()){ //si es cliente se crea un cliente con el usuario creado asociado
            $this->clienteRepository->create([
                'user_id' => $user->id
            ]);
        }else{
            $this->profesionalRepository->create([ //campos null el usuario los llena despue
                'user_id' => $user->id,
                'descripcion' => null,
                'modalidad_atencion' => null,
                'puntuacion_promedio' => 0
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;    
        
        return [
            'token' => $token,
            'user' => $user,
        ];
    }

}