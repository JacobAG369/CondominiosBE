<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.condominios', function () {
    return true;
});
