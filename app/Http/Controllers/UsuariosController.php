<?php

namespace App\Http\Controllers;

use App\Services\UsuarioService;
use Illuminate\Http\Request;

class UsuariosController extends Controller{
    
    private UsuarioService $usuarioService;

    public function __construct(UsuarioService $usuarioService){
        $this->usuarioService = $usuarioService;
    }

    public function buscarPorId(int $id){
        $user = $this->usuarioService->buscarPorId($id);

        if ($user->esProfesional()) {
            $user->load('profesional');
        }

        return response()->json($user, 200);
    }

    public function listarUsuarios(){
        $users = $this->usuarioService->listarUsuarios();

        return response()->json($users, 200);
    }

    public function editarUsuario(Request $request, int $id){
        if ($request->user()->id !== $id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $request->validate([
            'nombre' => 'nullable',
            'apellido' => 'nullable',
            'telefono' => 'nullable',
            'email' => 'nullable|email'
        ]);

        $user = $this->usuarioService->editarUsuario($request, $id);

        return response()->json($user, 200);
    }

}
