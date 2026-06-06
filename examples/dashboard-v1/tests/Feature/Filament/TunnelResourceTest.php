<?php

namespace Tests\Feature\Filament;

use Database\Seeders\TunnelSeeder;
use App\Filament\Resources\Tunnels\Pages\CreateTunnel;
use App\Filament\Resources\Tunnels\Pages\EditTunnel;
use App\Filament\Resources\Tunnels\Pages\ListTunnels;
use App\Filament\Resources\Tunnels\TunnelResource;
use App\Models\Tunnel;
use App\Models\User;
use App\Services\Tunnel\NgrokEnvSync;
use App\Services\Tunnel\NgrokTrafficPolicyWriter;
use App\Services\Tunnel\TunnelActivator;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class TunnelResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        config(['tunnel.enabled' => true]);
    }

    public function test_guest_cannot_access_tunnels_index(): void
    {
        $this->get(TunnelResource::getUrl('index'))
            ->assertRedirect('/admin/login');
    }

    public function test_staff_without_permission_cannot_access_tunnels(): void
    {
        $this->seedRoles();

        $user = User::factory()->create();
        $user->assignRole('staff');

        $this->actingAs($user)
            ->get(TunnelResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_admin_can_list_tunnels(): void
    {
        $user = $this->adminUser();
        Tunnel::factory()->create(['name' => 'Vitou static']);

        $this->actingAs($user)
            ->get(TunnelResource::getUrl('index'))
            ->assertOk()
            ->assertSee('Vitou static');
    }

    public function test_create_form_prefills_from_default_tunnel_profile(): void
    {
        config(['tunnel.enabled' => true]);
        $this->seed(TunnelSeeder::class);

        $user = $this->adminUser();
        $default = Tunnel::defaultTemplate();
        $this->assertNotNull($default);

        Livewire::actingAs($user)
            ->test(CreateTunnel::class)
            ->assertFormSet([
                'template_tunnel_id' => $default->id,
                'domain' => $default->domain,
                'herd_host' => $default->herd_host,
            ]);
    }

    public function test_create_form_template_picker_copies_profile_fields(): void
    {
        config(['tunnel.enabled' => true]);
        $this->seed(TunnelSeeder::class);

        $user = $this->adminUser();
        $staging = Tunnel::query()->where('name', 'Demo — staging')->first();
        $this->assertNotNull($staging);

        Livewire::actingAs($user)
            ->test(CreateTunnel::class)
            ->fillForm(['template_tunnel_id' => $staging->id])
            ->assertFormSet([
                'domain' => $staging->domain,
                'herd_host' => $staging->herd_host,
            ]);
    }

    public function test_admin_can_create_tunnel(): void
    {
        $user = $this->adminUser();

        Livewire::actingAs($user)
            ->test(CreateTunnel::class)
            ->fillForm([
                'name' => 'Team tunnel',
                'domain' => 'team-dev.ngrok-free.dev',
                'herd_host' => 'dashboard-v1.test',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('tunnels', [
            'name' => 'Team tunnel',
            'domain' => 'team-dev.ngrok-free.dev',
        ]);
    }

    public function test_activate_action_deactivates_other_tunnels(): void
    {
        $user = $this->adminUser();
        $envPath = tempnam(sys_get_temp_dir(), 'env');
        file_put_contents($envPath, "NGROK_DEV_DOMAIN=\n");
        $policyPath = $this->samplePolicyPath();

        $first = Tunnel::factory()->active()->create(['name' => 'Active one', 'domain' => 'one.ngrok-free.dev']);
        $second = Tunnel::factory()->create([
            'name' => 'Next one',
            'domain' => 'two.ngrok-free.dev',
            'herd_host' => 'alt-dashboard.test',
        ]);

        $this->app->bind(NgrokEnvSync::class, fn () => new NgrokEnvSync($envPath));
        $this->app->bind(NgrokTrafficPolicyWriter::class, fn () => new NgrokTrafficPolicyWriter($policyPath));
        $this->app->bind(TunnelActivator::class, fn ($app) => new TunnelActivator(
            $app->make(NgrokEnvSync::class),
            $app->make(NgrokTrafficPolicyWriter::class),
        ));

        Livewire::actingAs($user)
            ->test(ListTunnels::class)
            ->callTableAction('activate', $second)
            ->assertNotified();

        $this->assertFalse($first->fresh()->is_active);
        $this->assertTrue($second->fresh()->is_active);
        $this->assertStringContainsString('host: alt-dashboard.test', file_get_contents($policyPath));

        @unlink($envPath);
        @unlink($policyPath);
    }

    public function test_create_rejects_local_domain(): void
    {
        $user = $this->adminUser();

        Livewire::actingAs($user)
            ->test(CreateTunnel::class)
            ->fillForm([
                'name' => 'Bad local',
                'domain' => 'dashboard-v1.test',
                'herd_host' => 'dashboard-v1.test',
            ])
            ->call('create')
            ->assertHasFormErrors(['domain']);
    }

    public function test_create_rejects_duplicate_domain(): void
    {
        $user = $this->adminUser();
        Tunnel::factory()->create(['domain' => 'dup.ngrok-free.dev']);

        Livewire::actingAs($user)
            ->test(CreateTunnel::class)
            ->fillForm([
                'name' => 'Duplicate domain',
                'domain' => 'dup.ngrok-free.dev',
                'herd_host' => 'dashboard-v1.test',
            ])
            ->call('create')
            ->assertHasFormErrors(['domain']);
    }

    public function test_active_tunnel_cannot_be_deleted(): void
    {
        $user = $this->adminUser();
        $tunnel = Tunnel::factory()->active()->create();

        $this->assertFalse(TunnelResource::canDelete($tunnel));

        Livewire::actingAs($user)
            ->test(EditTunnel::class, ['record' => $tunnel->getRouteKey()])
            ->assertActionHidden(DeleteAction::class);
    }

    public function test_verify_action_updates_health_status(): void
    {
        Http::fake([
            'https://probe.ngrok-free.dev/login' => Http::response('ok', 200),
        ]);

        $user = $this->adminUser();
        $tunnel = Tunnel::factory()->create(['domain' => 'probe.ngrok-free.dev']);

        Livewire::actingAs($user)
            ->test(ListTunnels::class)
            ->callTableAction('verify', $tunnel)
            ->assertNotified();

        $tunnel->refresh();
        $this->assertSame('ok', $tunnel->last_verified_status);
    }

    public function test_tunnel_admin_hidden_when_feature_disabled(): void
    {
        config(['tunnel.enabled' => false]);
        $user = $this->adminUser();

        $this->actingAs($user)
            ->get(TunnelResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_user_with_permission_but_not_admin_role_can_access_when_granted(): void
    {
        $this->seedRoles();

        Permission::findOrCreate('manage_dev_tunnels');

        $user = User::factory()->create();
        $user->givePermissionTo(['access_admin', 'manage_dev_tunnels']);

        $this->actingAs($user)
            ->get(TunnelResource::getUrl('index'))
            ->assertOk();
    }

    private function samplePolicyPath(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'policy');
        file_put_contents($path, <<<'YAML'
# Herd routes by Host; ngrok must send dashboard-v1.test upstream.
on_http_request:
  - actions:
      - type: add-headers
        config:
          headers:
            host: dashboard-v1.test
YAML);

        return $path;
    }
}
