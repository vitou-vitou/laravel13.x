<?php

namespace App\Console\Commands;

use App\Services\Booking\BookingService;
use Illuminate\Console\Command;

class ReleaseExpiredBookingHolds extends Command
{
    protected $signature = 'bookings:release-expired-holds';

    protected $description = 'Mark expired booking holds as expired';

    public function handle(BookingService $bookings): int
    {
        $count = $bookings->releaseExpiredHolds();
        $this->info("Released {$count} expired hold(s).");

        return self::SUCCESS;
    }
}
