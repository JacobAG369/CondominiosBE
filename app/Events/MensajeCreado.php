<?php

namespace App\Events;

use App\Models\Mensaje;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MensajeCreado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Mensaje $mensaje;

    public function __construct(Mensaje $mensaje)
    {
        $this->mensaje = $mensaje;
    }

    public function broadcastOn(): array
    {
        return [new Channel('chat.condominios')];
    }

    public function broadcastAs(): string
    {
        return 'mensaje.creado';
    }

    public function broadcastWith(): array
    {
        return [
            'mensaje' => $this->mensaje->toArray(),
        ];
    }
}
