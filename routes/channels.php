<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});


Broadcast::channel('chat.{a}.{b}', function ($user, $a, $b) {
    return (int)$user->id === (int)$a || (int)$user->id === (int)$b;
});

