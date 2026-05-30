<?php

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('profesional.{id}', function ($user, $id) {
    return $user && $user->esProfesional() && optional($user->profesional)->id == (int) $id;
});

Broadcast::channel('usuario.{id}', function ($user, $id) {
    return $user && $user->id == (int) $id;
});

Broadcast::channel('reserva.{id}', function ($user, $id) {
    return $user != null;
});

Broadcast::channel('presence:chat.conversation.{id}', function ($user, $id) {
    return ['id' => $user->id, 'nombre' => $user->nombre ?? $user->name ?? ''];
});
