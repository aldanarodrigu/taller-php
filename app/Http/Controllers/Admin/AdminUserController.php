<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class AdminUserController extends Controller
{
    public function index()
    {
        return response()->json(
            User::select('id', 'nombre', 'apellido', 'email', 'role', 'created_at')->get()
        );
    }

    public function show(User $user){
    return response()->json([
        'usuario' => $user, 'actividades' => $user->actividades() ->latest() ->get()
    ]);
}

}