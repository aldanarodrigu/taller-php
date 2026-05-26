<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Repositories\UserRepository;
use App\Models\User;
use Exception;

class UsuarioService{

    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository){
        $this->userRepository = $userRepository;
    }

    public function buscarPorId(int $id){
        $user = $this->userRepository->findById($id);

        if(!$user){
            throw new Exception('Usuario no encontrado', 401);
        }

        return $user;
    }

    public function listarUsuarios(){
        return $this->userRepository->findAll();
    }

    public function editarUsuario(Request $request, int $id){

        $user = $this->userRepository->findById($id);

        if(!$user){
            throw new Exception("Usuario no encontrado");
        }

        $user->update([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'telefono' => $request->telefono,
            'email' => $request->email
        ]);

        return $user;
    }

}