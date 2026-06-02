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
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_another_customer_cannot_confirm_someone_elses_booking(): void
    {
        $provider = User::factory()->create(['role' => UserRole::Provider]);
        $profile = ProviderProfile::query()->create([
            'user_id' => $provider->id,
            'business_name' => 'Clinic',
            'timezone' => 'UTC',
        ]);
        $resource = BookableResource::query()->create([
            'provider_profile_id' => $profile->id,
            'name' => 'Room 1',
        ]);
        AvailabilityRule::query()->create([
            'bookable_resource_id' => $resource->id,
            'day_of_week' => Carbon::MONDAY,
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);
        $service = Service::query()->create([
            'provider_profile_id' => $profile->id,
            'name' => 'Visit',
            'duration_minutes' => 30,
        ]);

        $owner = User::factory()->create(['role' => UserRole::Customer]);
        $intruder = User::factory()->create(['role' => UserRole::Customer]);

        $booking = Booking::query()->create([
            'bookable_resource_id' => $resource->id,
            'service_id' => $service->id,
            'customer_id' => $owner->id,
            'starts_at' => now()->addDays(3),
            'ends_at' => now()->addDays(3)->addMinutes(30),
            'status' => BookingStatus::Held,
            'hold_expires_at' => now()->addMinutes(10),
        ]);

        $this->actingAsApi($intruder)
            ->postJson("/api/bookings/{$booking->id}/confirm")
            ->assertForbidden();
    }
}
