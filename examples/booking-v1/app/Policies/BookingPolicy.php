<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function view(User $user, Booking $booking): bool
    {
        return $this->ownsBooking($user, $booking);
    }

    public function confirm(User $user, Booking $booking): bool
    {
        return $this->ownsBooking($user, $booking);
    }

    public function cancel(User $user, Booking $booking): bool
    {
        return $this->ownsBooking($user, $booking);
    }

    private function ownsBooking(User $user, Booking $booking): bool
    {
        return $booking->customer_id === $user->id;
    }
}
