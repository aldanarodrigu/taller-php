<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthService;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class AuthController extends Controller{
    
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function registrar(Request $request){

        $request->validate([
            'nombre' => 'required',
            'apellido' => 'required',
            'telefono' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:cliente,profesional'
        ]);

        $data = $this->authService->registrar($request);
        
        return response()->json($data,201);
    }

    public function login(Request $request){

        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        try {
            $data = $this->authService->login($request);
            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }

    }

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente'
        ], 200);
    }

    public function getAuthenticatedUser(Request $request) {
        return response()->json($request->user(), 200);
    }

    public function redirectGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callbackGoogle()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::firstOrCreate(
            ['email' => $googleUser->email],
            [
                'nombre' => $googleUser->name,
                'apellido' => '-',
                'telefono' => '-',
                'role' => 'cliente',
                'google_id' => $googleUser->id,
                'password' => bcrypt(uniqid())
            ]
        );

        $token = $user->createToken('auth_token')->plainTextToken;
        
        return redirect(env('FRONTEND_URL') . '/auth/callback?token=' . $token);
    }

}
