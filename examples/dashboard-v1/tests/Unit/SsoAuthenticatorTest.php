<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\SsoAuthenticator;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class SsoAuthenticatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticate_creates_user_with_customer_role(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $socialiteUser = new SocialiteUser;
        $socialiteUser->id = 'google-unit-1';
        $socialiteUser->name = 'Unit SSO';
        $socialiteUser->email = 'unit-sso@example.com';

        $user = app(SsoAuthenticator::class)->authenticate('google', $socialiteUser);

        $this->assertSame('unit-sso@example.com', $user->email);
        $this->assertTrue($user->hasRole('customer'));
        $this->assertDatabaseHas('users', [
            'email' => 'unit-sso@example.com',
            'sso_provider' => 'google',
            'sso_id' => 'google-unit-1',
        ]);
    }
}
