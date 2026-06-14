<?php

namespace Tests\Feature;

use App\Enums\PublishStatus;
use App\Models\Creator;
use App\Models\IntegrationWebhook;
use App\Models\IntegrationWebhookDelivery;
use App\Models\PublishLogEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IntegrationWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_fires_when_creator_approves(): void
    {
        Http::fake(['*' => Http::response('ok', 200)]);

        User::factory()->operator()->create();
        $creatorUser = User::factory()->creator()->create();
        $creator = Creator::factory()->create(['user_id' => $creatorUser->id]);
        $entry = PublishLogEntry::factory()->create([
            'creator_id' => $creator->id,
            'status' => PublishStatus::PendingApproval,
        ]);

        IntegrationWebhook::factory()->create([
            'url' => 'https://hooks.example.com/approved',
            'on_approved' => true,
            'on_published' => false,
        ]);

        $this->actingAs($creatorUser)
            ->post(route('creator.approvals.approve', $entry))
            ->assertRedirect();

        Http::assertSent(fn ($request) => $request->url() === 'https://hooks.example.com/approved'
            && $request['event'] === 'publish_log.approved');

        $this->assertSame(1, IntegrationWebhookDelivery::query()->count());
    }

    public function test_operator_can_add_and_test_webhook(): void
    {
        Http::fake(['*' => Http::response('pong', 200)]);

        $operator = User::factory()->operator()->create();

        $this->actingAs($operator)
            ->post(route('operator.integrations.store'), [
                'url' => 'https://hooks.example.com/test',
                'on_approved' => true,
                'on_published' => true,
            ])
            ->assertRedirect();

        $webhook = IntegrationWebhook::query()->first();
        $this->assertNotNull($webhook);

        $this->actingAs($operator)
            ->post(route('operator.integrations.test', $webhook))
            ->assertRedirect();

        Http::assertSent(fn ($request) => $request->url() === 'https://hooks.example.com/test');
    }
}
