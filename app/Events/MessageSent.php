<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class MessageSent implements ShouldBroadcastNow
{
    public function __construct(public Message $message) {}

    public function broadcastOn(): Channel
    {
        // chat global
        return new Channel('chat.global');
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'from_depa_id' => $this->message->from_depa_id,
            'content' => $this->message->content,
            'created_at' => $this->message->created_at?->toISOString(),
        ];
    }
}
