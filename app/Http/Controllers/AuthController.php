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
            'telefono' => 'nullable',
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
        $user = $request->user();
        
        if ($user->esProfesional()) {
            $user->load('profesional');
        }

        return response()->json($user, 200);
    }

    public function redirectGoogle(Request $request)
    {
        $callbackUrl = rtrim($request->getSchemeAndHttpHost(), '/') . '/auth/google/callback';

        return Socialite::driver('google')
            ->redirectUrl($callbackUrl)
            ->redirect();
    }

    public function callbackGoogle(Request $request)
    {
        $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/');

        if ($request->has('error') || !$request->has('code')) {
            $error = $request->get('error', 'oauth_invalid_request');
            return redirect($frontendUrl . '/auth/login?error=' . urlencode($error));
        }

        $callbackUrl = rtrim($request->getSchemeAndHttpHost(), '/') . '/auth/google/callback';

        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl($callbackUrl)
                ->user();
        } catch (\Throwable $e) {
            return redirect($frontendUrl . '/auth/login?error=' . urlencode('oauth_callback_failed'));
        }

        // si usuario existe hace login directo
        $existing = User::where('email', $googleUser->email)->first();

        if ($existing) {
            if (!$existing->google_id) {
                $existing->update(['google_id' => $googleUser->id]);
            }
            $token = $existing->createToken('auth_token')->plainTextToken;
            return redirect($frontendUrl . '/auth/callback?token=' . $token);
        }

        // Usuario nuevo frontend elige rol
        $payload = encrypt(json_encode([
            'google_id' => $googleUser->id,
            'nombre'    => $googleUser->name,
            'email'     => $googleUser->email,
            'expires_at' => now()->addMinutes(15)->timestamp,
        ]));

        return redirect($frontendUrl . '/auth/google/select-rol?payload=' . urlencode($payload));
    }

    public function completeGoogleRegister(Request $request)
    {
        $request->validate([
            'payload' => 'required|string',
            'role'    => 'required|in:cliente,profesional',
        ]);

        try {
            $data = json_decode(decrypt($request->payload), true);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Payload inválido.'], 422);
        }

        if ($data['expires_at'] < now()->timestamp) {
            return response()->json(['message' => 'El enlace expiró. Intentá de nuevo.'], 422);
        }

        if (User::where('email', $data['email'])->exists()) {
            return response()->json(['message' => 'Este email ya está registrado.'], 409);
        }

        $result = $this->authService->registrarConGoogle([
            'nombre'    => $data['nombre'],
            'email'     => $data['email'],
            'google_id' => $data['google_id'],
            'role'      => $request->role,
        ]);

        return response()->json($result, 201);
    }

}
