<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\NotificacionRepository;

class NotificacionService
{
    public function __construct(
        private NotificacionRepository $repository
    ) {}

    public function listar(User $user, ?bool $leida = null)
    {
        return $this->repository->getByUser($user, $leida);
    }

    public function marcarComoLeida(User $user, string $id)
    {
        $notification = $this->repository->findByUserAndId($user, $id);
        $notification->markAsRead();
        return $notification;
    }

    public function marcarTodasComoLeidas(User $user): void
    {
        $this->repository->markAllAsRead($user);
    }

    public function eliminar(User $user, string $id): void
    {
        $this->repository->delete($user, $id);
    }
}