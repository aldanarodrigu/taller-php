<?php

namespace App\Services;

use App\Models\Reserva;
use App\Repositories\ReservaRepository;
use Exception;
use Illuminate\Http\Request;

class LiveKitService
{
    public function __construct(
        private ReservaRepository $reservaRepository
    ) {}

    public function generarToken(Request $request, int $reservaId): array
    {
        $reserva = $this->reservaRepository->findById($reservaId);

        if (!$reserva) {
            throw new Exception('Reserva no encontrada', 404);
        }

        $reserva->load('servicio');

        $this->verificarAcceso($request, $reserva);
        $this->verificarEstado($reserva);

        $room = $reserva->livekit_room ?? 'reserva-' . $reservaId;
        $identity = 'user-' . $request->user()->id;
        $token = $this->crearJwt($room, $identity);

        $reserva->update([
            'livekit_room'  => $room,
            'livekit_token' => $token,
            'estado'        => 'en_curso',
        ]);

        return [
            'token' => $token,
            'room'  => $room,
            'url'   => config('services.livekit.url', 'wss://tu-servidor-livekit'),
        ];
    }

    public function renovarToken(Request $request, int $reservaId): array
    {
        return $this->generarToken($request, $reservaId);
    }

    public function finalizar(Request $request, int $reservaId): Reserva
    {
        $reserva = $this->reservaRepository->findById($reservaId);

        if (!$reserva) {
            throw new Exception('Reserva no encontrada', 404);
        }

        $reserva->load('servicio');

        $this->verificarAcceso($request, $reserva);

        if (!in_array($reserva->estado, ['confirmada', 'pagada', 'en_curso'])) {
            throw new Exception('La reserva no se puede finalizar en su estado actual', 422);
        }

        $reserva->update(['estado' => 'finalizada']);

        return $reserva;
    }

    // Privados

    private function verificarAcceso(Request $request, Reserva $reserva): void
    {
        $user = $request->user();

        $esCliente = $user->esCliente()
            && $user->cliente
            && $reserva->cliente_id === $user->cliente->id;

        $esProfesional = $user->esProfesional()
            && $user->profesional
            && $reserva->servicio->profesional_id === $user->profesional->id;

        if (!$esCliente && !$esProfesional) {
            throw new Exception('No tenés acceso a esta videollamada', 403);
        }
    }

    private function verificarEstado(Reserva $reserva): void
    {
        if (!in_array($reserva->estado, ['confirmada', 'pagada', 'en_curso'])) {
            throw new Exception('La reserva debe estar confirmada o pagada para iniciar la videollamada', 422);
        }
    }

    private function crearJwt(string $room, string $identity): string
    {
        $apiKey    = config('services.livekit.key');
        $apiSecret = config('services.livekit.secret');

        if (!$apiKey || !$apiSecret) {
            throw new Exception('LiveKit no está configurado', 500);
        }

        $now = time();

        $header = $this->base64url(json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT',
        ]));

        $payload = $this->base64url(json_encode([
            'iss'   => $apiKey,
            'sub'   => $identity,
            'iat'   => $now,
            'exp'   => $now + 3600,
            'video' => [
                'room'         => $room,
                'roomJoin'     => true,
                'canPublish'   => true,
                'canSubscribe' => true,
            ],
        ]));

        $firma = $this->base64url(
            hash_hmac('sha256', "$header.$payload", $apiSecret, true)
        );

        return "$header.$payload.$firma";
    }

    private function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}