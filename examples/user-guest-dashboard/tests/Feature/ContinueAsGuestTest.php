<?php

namespace Tests\Feature;

use App\Filament\Auth\GuestLogin;
use App\Models\Guest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContinueAsGuestTest extends TestCase
{
    use RefreshDatabase;

    public function test_continue_as_guest_creates_guest_and_authenticates(): void
    {
        $this->assertDatabaseCount('guests', 0);

        Livewire::test(GuestLogin::class)
            ->call('continueAsGuest')
            ->assertRedirect();

        $this->assertDatabaseCount('guests', 1);
        $this->assertAuthenticatedAs(Guest::query()->first(), 'guest');
    }
}
