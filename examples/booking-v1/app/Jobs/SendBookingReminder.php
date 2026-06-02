<?php

namespace App\Jobs;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendBookingReminder implements ShouldQueue
{
    use Queueable;

    public function __construct(public Booking $booking) {}

    public function handle(): void
    {
        $booking = $this->booking->fresh();

        if ($booking === null || $booking->status !== BookingStatus::Confirmed) {
            return;
        }

        Log::info('booking.reminder', [
            'booking_id' => $booking->id,
            'customer_id' => $booking->customer_id,
            'starts_at' => $booking->starts_at->toIso8601String(),
        ]);
    }
}
