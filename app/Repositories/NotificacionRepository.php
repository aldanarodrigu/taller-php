<?php

namespace App\Repositories;

use App\Models\User;

class NotificacionRepository
{
    public function getByUser(User $user, ?bool $leida = null)
    {
        $query = $user->notifications()->latest();

        if ($leida !== null) {
            $leida ? $query->whereNotNull('read_at') 
                   : $query->whereNull('read_at');
        }

        return $query->paginate(20);
    }

    public function findByUserAndId(User $user, string $id)
    {
        return $user->notifications()->findOrFail($id);
    }

    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }

    public function delete(User $user, string $id): void
    {
        $user->notifications()->findOrFail($id)->delete();
    }
}