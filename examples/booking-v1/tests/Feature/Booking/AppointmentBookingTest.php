<?php

namespace Tests\Feature\Booking;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\AvailabilityRule;
use App\Models\BookableResource;
use App\Models\Booking;
use App\Models\ProviderProfile;
use App\Models\Service;
use App\Models\User;
use App\Services\Booking\BookingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_setup_exposes_bookable_slots(): void
    {
        [$service, $slot] = $this->providerWithSlot();

        $customer = User::factory()->create(['role' => UserRole::Customer]);

        $response = $this->actingAsApi($customer)
            ->getJson("/api/services/{$service->id}/slots?from={$slot->toDateString()}&to={$slot->toDateString()}");

        $response->assertOk();
        $this->assertContains($slot->toIso8601String(), $response->json('data'));
    }

    public function test_customer_can_hold_and_confirm_booking(): void
    {
        [$service, $slot] = $this->providerWithSlot();
        $customer = User::factory()->create(['role' => UserRole::Customer]);

        $hold = $this->actingAsApi($customer)
            ->postJson("/api/services/{$service->id}/bookings/hold", [
                'starts_at' => $slot->toIso8601String(),
            ]);

        $hold->assertCreated();
        $bookingId = $hold->json('data.id');

        $confirm = $this->actingAsApi($customer)
            ->postJson("/api/bookings/{$bookingId}/confirm");

        $confirm->assertOk();
        $this->assertSame(BookingStatus::Confirmed->value, $confirm->json('data.status'));

        $slots = $this->actingAsApi($customer)
            ->getJson("/api/services/{$service->id}/slots?from={$slot->toDateString()}&to={$slot->toDateString()}");

        $this->assertNotContains($slot->toIso8601String(), $slots->json('data'));
    }

    public function test_second_customer_cannot_hold_same_slot(): void
    {
        [$service, $slot] = $this->providerWithSlot();
        $customerA = User::factory()->create(['role' => UserRole::Customer]);
        $customerB = User::factory()->create(['role' => UserRole::Customer]);
        $bookings = app(BookingService::class);

        $bookings->hold($customerA, $service, $slot);

        $this->expectException(\InvalidArgumentException::class);
        $bookings->hold($customerB, $service, $slot);
    }

    public function test_expired_hold_releases_slot(): void
    {
        [$service, $slot] = $this->providerWithSlot();

        Booking::query()->create([
            'bookable_resource_id' => $service->providerProfile->resources()->first()->id,
            'service_id' => $service->id,
            'customer_id' => User::factory()->create()->id,
            'starts_at' => $slot,
            'ends_at' => $slot->copy()->addMinutes($service->duration_minutes),
            'status' => BookingStatus::Held,
            'hold_expires_at' => now()->subMinute(),
        ]);

        app(BookingService::class)->releaseExpiredHolds();

        $customer = User::factory()->create(['role' => UserRole::Customer]);
        $slots = $this->actingAsApi($customer)
            ->getJson("/api/services/{$service->id}/slots?from={$slot->toDateString()}&to={$slot->toDateString()}");

        $this->assertContains($slot->toIso8601String(), $slots->json('data'));
    }

    public function test_cancel_within_policy_frees_slot(): void
    {
        [$service, $slot] = $this->providerWithSlot();
        $customer = User::factory()->create(['role' => UserRole::Customer]);

        $futureSlot = $slot->copy()->addWeek();

        $booking = app(BookingService::class)->hold($customer, $service, $futureSlot);
        app(BookingService::class)->confirm($booking);

        app(BookingService::class)->cancel($booking->fresh(), $customer);

        $slots = $this->actingAsApi($customer)
            ->getJson("/api/services/{$service->id}/slots?from={$futureSlot->toDateString()}&to={$futureSlot->toDateString()}");

        $this->assertContains($futureSlot->toIso8601String(), $slots->json('data'));
    }

    public function test_cancel_outside_policy_is_rejected(): void
    {
        [$service, $slot] = $this->providerWithSlot();
        $customer = User::factory()->create(['role' => UserRole::Customer]);

        $soon = now()->addHours(2);

        $booking = Booking::query()->create([
            'bookable_resource_id' => $service->providerProfile->resources()->first()->id,
            'service_id' => $service->id,
            'customer_id' => $customer->id,
            'starts_at' => $soon,
            'ends_at' => $soon->copy()->addMinutes($service->duration_minutes),
            'status' => BookingStatus::Confirmed,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        app(BookingService::class)->cancel($booking, $customer);
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
