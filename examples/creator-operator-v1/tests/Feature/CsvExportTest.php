<?php

namespace Tests\Feature;

use App\Enums\PublishStatus;
use App\Enums\SettlementPlatform;
use App\Models\Creator;
use App\Models\MonthlySettlement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CsvExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_can_export_publish_log_csv(): void
    {
        $operator = User::factory()->operator()->create();
        $creator = Creator::factory()->create();

        $creator->publishLogEntries()->create([
            'logged_on' => '2026-06-10',
            'tiktok_url' => 'https://www.tiktok.com/@pilot/video/1',
            'title_variant' => 'Export me',
            'status' => PublishStatus::Published,
            'views_yt_7d' => 100,
        ]);

        $response = $this->actingAs($operator)->get(route('operator.creators.publish-log.export', $creator));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('tiktok_url', $response->streamedContent());
        $this->assertStringContainsString('Export me', $response->streamedContent());
    }

    public function test_operator_can_export_settlement_csv(): void
    {
        $operator = User::factory()->operator()->create();
        $creator = Creator::factory()->create();

        MonthlySettlement::factory()->create([
            'creator_id' => $creator->id,
            'platform' => SettlementPlatform::Youtube,
            'attributed_revenue' => 41.67,
            'commission_amount' => 6.25,
            'creator_net' => 35.42,
        ]);

        $response = $this->actingAs($operator)->get(route('operator.creators.settlement.export', $creator));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('attributed_revenue', $response->streamedContent());
        $this->assertStringContainsString('41.67', $response->streamedContent());
    }
}
