<?php

namespace Tests\Feature\Booking;

use App\Enums\UserRole;
use App\Jobs\SendBookingReminder;
use App\Models\AvailabilityRule;
use App\Models\BookableResource;
use App\Models\Booking;
use App\Models\ProviderProfile;
use App\Models\Service;
use App\Models\User;
use App\Services\Booking\BookingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class SendBookingReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirming_booking_dispatches_reminder_job_when_starts_more_than_24h_ahead(): void
    {
        Bus::fake();

        [$service, $slot] = $this->providerWithSlot();
        $customer = User::factory()->create(['role' => UserRole::Customer]);
        $bookings = app(BookingService::class);

        $booking = $bookings->hold($customer, $service, $slot);
        $bookings->confirm($booking);

        Bus::assertDispatched(SendBookingReminder::class, function (SendBookingReminder $job) use ($booking) {
            return $job->booking->is($booking->fresh());
        });
    }

    public function test_confirming_booking_skips_reminder_when_starts_within_24h(): void
    {
        Bus::fake();

        [$service, $slot] = $this->providerWithSlot();
        Carbon::setTestNow($slot->copy()->subHours(12));

        $customer = User::factory()->create(['role' => UserRole::Customer]);
        $bookings = app(BookingService::class);

        $booking = $bookings->hold($customer, $service, $slot);
        $bookings->confirm($booking);

        Bus::assertNotDispatched(SendBookingReminder::class);

        Carbon::setTestNow();
    }

    /**
     * @return array{0: Service, 1: Carbon}
     */
    private function providerWithSlot(): array
    {
        $provider = User::factory()->create(['role' => UserRole::Provider]);
        $profile = ProviderProfile::query()->create([
            'user_id' => $provider->id,
            'business_name' => 'Test Spa',
            'timezone' => 'UTC',
        ]);
        $resource = BookableResource::query()->create([
            'provider_profile_id' => $profile->id,
            'name' => 'Room 1',
        ]);

        $nextMonday = now()->next(Carbon::MONDAY)->startOfDay();
        if ($nextMonday->isPast()) {
            $nextMonday = $nextMonday->addWeek();
        }

        AvailabilityRule::query()->create([
            'bookable_resource_id' => $resource->id,
            'day_of_week' => $nextMonday->dayOfWeek,
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $service = Service::query()->create([
            'provider_profile_id' => $profile->id,
            'name' => 'Consultation',
            'duration_minutes' => 30,
        ]);

        $slot = $nextMonday->copy()->setTime(10, 0);

        return [$service, $slot];
    }
}
