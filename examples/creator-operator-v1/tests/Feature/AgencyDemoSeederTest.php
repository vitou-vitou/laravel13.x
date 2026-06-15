<?php

namespace Tests\Feature;

use App\Enums\OperatorPlan;
use App\Enums\PublishStatus;
use App\Models\Creator;
use App\Models\PublishLogEntry;
use App\Models\User;
use App\Models\WeeklyMetric;
use Database\Seeders\AgencyDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgencyDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    private function enableAgencyDemoSeed(): void
    {
        putenv('SEED_AGENCY_DEMO=true');
        $_ENV['SEED_AGENCY_DEMO'] = 'true';
        $_SERVER['SEED_AGENCY_DEMO'] = 'true';
    }

    private function disableAgencyDemoSeed(): void
    {
        putenv('SEED_AGENCY_DEMO=false');
        $_ENV['SEED_AGENCY_DEMO'] = 'false';
        $_SERVER['SEED_AGENCY_DEMO'] = 'false';
    }

    protected function tearDown(): void
    {
        putenv('SEED_AGENCY_DEMO');
        unset($_ENV['SEED_AGENCY_DEMO'], $_SERVER['SEED_AGENCY_DEMO']);

        parent::tearDown();
    }

    public function test_seeder_skipped_when_flag_is_false(): void
    {
        $this->disableAgencyDemoSeed();

        User::factory()->operator()->create();

        $this->seed(AgencyDemoSeeder::class);

        $this->assertDatabaseCount('creators', 0);
    }

    public function test_agency_demo_seeder_creates_roster_with_publish_logs(): void
    {
        $this->enableAgencyDemoSeed();

        $operator = User::factory()->operator()->create([
            'operator_plan' => OperatorPlan::Starter,
        ]);

        $this->seed(AgencyDemoSeeder::class);

        $this->assertSame(OperatorPlan::Demo, $operator->fresh()->operator_plan);
        $this->assertSame(100, Creator::query()->where('handle', 'like', 'agency_creator_%')->count());
        $this->assertSame(
            100,
            Creator::query()->where('onboarding_notes', 'Agency demo roster')->count()
        );

        $publishCount = PublishLogEntry::query()
            ->whereHas('creator', fn ($q) => $q->where('handle', 'like', 'agency_creator_%'))
            ->count();

        $this->assertGreaterThanOrEqual(400, $publishCount);
        $this->assertLessThanOrEqual(500, $publishCount);
        $this->assertSame(100, WeeklyMetric::query()->count());

        $this->assertGreaterThan(0, PublishLogEntry::query()
            ->where('status', PublishStatus::PendingApproval)
            ->count());
        $this->assertGreaterThan(0, PublishLogEntry::query()
            ->where('status', PublishStatus::Published)
            ->whereNotNull('views_yt_7d')
            ->count());

        $first = Creator::query()->where('handle', 'agency_creator_001')->firstOrFail();
        $this->assertSame('https://www.tiktok.com/@agency_creator_001', $first->tiktok_url);
        $this->assertNull($first->user_id);
    }

    public function test_artisan_class_seeder_respects_flag(): void
    {
        $this->enableAgencyDemoSeed();

        User::factory()->operator()->create();

        $this->seed(AgencyDemoSeeder::class);

        $this->assertSame(100, Creator::query()->count());
    }
}
