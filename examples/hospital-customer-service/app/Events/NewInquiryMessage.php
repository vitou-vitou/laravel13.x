<?php

namespace App\Events;

use App\Models\InquiryMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewInquiryMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public InquiryMessage $message)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('inquiry.'.$this->message->inquiry_id),
            new Channel('staff.inquiries'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'inquiry_id' => $this->message->inquiry_id,
            'sender_type' => $this->message->sender_type,
            'sender_name' => $this->message->sender_name,
            'message' => $this->message->message,
            'created_at' => $this->message->created_at->toIso8601String(),
        ];
    }
}
