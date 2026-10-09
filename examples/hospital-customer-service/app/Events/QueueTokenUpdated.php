<?php

namespace App\Events;

use App\Models\QueueToken;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueueTokenUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public QueueToken $token)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('queue.'.$this->token->department_id),
            new Channel('token.'.$this->token->token_number),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'token_number' => $this->token->token_number,
            'department_id' => $this->token->department_id,
            'status' => $this->token->status,
            'counter_assigned' => $this->token->counter_assigned,
            'called_at' => $this->token->called_at?->toIso8601String(),
        ];
    }
}
