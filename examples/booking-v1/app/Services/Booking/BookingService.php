<?php

namespace App\Services\Booking;

use App\Enums\BookingStatus;
use App\Jobs\SendBookingReminder;
use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BookingService
{
    public function __construct(
        private readonly SlotCalculator $slotCalculator,
        private readonly int $holdMinutes = 10,
        private readonly int $cancellationCutoffHours = 24,
    ) {}

    public function hold(User $customer, Service $service, CarbonInterface $startsAt): Booking
    {
        $this->assertFuture($startsAt);
        $resource = $service->providerProfile->resources()->firstOrFail();
        $endsAt = $startsAt->copy()->addMinutes($service->duration_minutes);

        $this->assertSlotAvailable($service, $resource, $startsAt, $endsAt);

        return Booking::query()->create([
            'bookable_resource_id' => $resource->id,
            'service_id' => $service->id,
            'customer_id' => $customer->id,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => BookingStatus::Held,
            'hold_expires_at' => now()->addMinutes($this->holdMinutes),
        ]);
    }

    public function confirm(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking) {
            $booking = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            if ($booking->status === BookingStatus::Confirmed) {
                return $booking;
            }

            if ($booking->status === BookingStatus::Held && $booking->hold_expires_at?->isPast()) {
                throw new InvalidArgumentException('Hold has expired.');
            }

            if (! in_array($booking->status, [BookingStatus::Held, BookingStatus::Confirmed], true)) {
                throw new InvalidArgumentException('Booking cannot be confirmed.');
            }

            $overlap = Booking::query()
                ->where('bookable_resource_id', $booking->bookable_resource_id)
                ->where('id', '!=', $booking->id)
                ->where('starts_at', '<', $booking->ends_at)
                ->where('ends_at', '>', $booking->starts_at)
                ->where(function ($q) {
                    $q->where('status', BookingStatus::Confirmed)
                        ->orWhere(function ($q2) {
                            $q2->where('status', BookingStatus::Held)
                                ->where('hold_expires_at', '>', now());
                        });
                })
                ->exists();

            if ($overlap) {
                throw new InvalidArgumentException('Slot is no longer available.');
            }

            $booking->update([
                'status' => BookingStatus::Confirmed,
                'hold_expires_at' => null,
            ]);

            $booking = $booking->fresh();
            $this->scheduleReminder($booking);

            return $booking;
        });
    }

    public function cancel(Booking $booking, User $actor): Booking
    {
        $booking->loadMissing('bookableResource.providerProfile');

        if ($booking->status !== BookingStatus::Confirmed && $booking->status !== BookingStatus::Held) {
            throw new InvalidArgumentException('Booking cannot be cancelled.');
        }

        $timezone = $booking->bookableResource?->providerProfile?->timezone ?? 'UTC';
        $startsAt = $booking->starts_at->copy()->timezone($timezone);
        $hoursUntil = now()->timezone($timezone)->diffInHours($startsAt, false);

        if ($hoursUntil < $this->cancellationCutoffHours) {
            throw new InvalidArgumentException('Cancellation window has passed.');
        }

        $booking->update([
            'status' => BookingStatus::Cancelled,
            'cancelled_at' => now(),
            'hold_expires_at' => null,
        ]);

        return $booking->fresh();
    }

    private function scheduleReminder(Booking $booking): void
    {
        $reminderAt = $booking->starts_at->copy()->subHours(24);

        if ($reminderAt->isFuture()) {
            SendBookingReminder::dispatch($booking)->delay($reminderAt);
        }
    }

    public function releaseExpiredHolds(): int
    {
        return Booking::query()
            ->where('status', BookingStatus::Held)
            ->where('hold_expires_at', '<=', now())
            ->update(['status' => BookingStatus::Expired]);
    }

    private function assertFuture(CarbonInterface $startsAt): void
    {
        if ($startsAt->isPast()) {
            throw new InvalidArgumentException('Cannot book in the past.');
        }
    }

    private function assertSlotAvailable(Service $service, $resource, CarbonInterface $startsAt, CarbonInterface $endsAt): void
    {
        $available = $this->slotCalculator->availableStarts(
            $service,
            $resource,
            $startsAt->copy()->subMinute(),
            $startsAt->copy()->addMinute(),
        );

        if (! $available->contains(fn (CarbonInterface $s) => $s->equalTo($startsAt))) {
            throw new InvalidArgumentException('Slot is not available.');
        }
    }
}
