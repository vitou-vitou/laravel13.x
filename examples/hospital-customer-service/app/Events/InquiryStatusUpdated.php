<?php

namespace App\Events;

use App\Models\Inquiry;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InquiryStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Inquiry $inquiry)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('inquiry.'.$this->inquiry->id),
            new Channel('staff.inquiries'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->inquiry->id,
            'status' => $this->inquiry->status,
            'department_id' => $this->inquiry->department_id,
            'department_name' => $this->inquiry->department?->name,
            'assigned_staff_id' => $this->inquiry->assigned_staff_id,
            'assigned_staff_name' => $this->inquiry->assignedStaff?->name,
        ];
    }
}
