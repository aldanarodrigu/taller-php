<?php

namespace App\Http\Controllers;

use App\Services\NotificacionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificacionController extends Controller
{
    public function __construct(
        private NotificacionService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $leida = $request->has('leida') ? $request->boolean('leida') : null;

        return response()->json(
            $this->service->listar($request->user(), $leida)
        );
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);

        return response()->json($notification);
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $this->service->marcarComoLeida($request->user(), $id);

        return response()->json($notification);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $this->service->marcarTodasComoLeidas($request->user());

        return response()->json(['message' => 'Todas las notificaciones marcadas como leídas']);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->service->eliminar($request->user(), $id);

        return response()->json(null, 204);
    }
}