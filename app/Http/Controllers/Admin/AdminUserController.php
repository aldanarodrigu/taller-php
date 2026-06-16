<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class AdminUserController extends Controller
{
    public function index()
    {
        return response()->json(
            User::select('id', 'nombre', 'apellido', 'email', 'role')->get()
        );
    }
}