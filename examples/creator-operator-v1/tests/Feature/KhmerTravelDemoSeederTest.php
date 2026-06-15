<?php

namespace Tests\Feature;

use App\Enums\OperatorPlan;
use App\Models\Creator;
use App\Models\PublishLogEntry;
use App\Models\User;
use App\Models\WeeklyMetric;
use Database\Seeders\KhmerTravelDemoSeeder;
use Database\Seeders\Support\KhmerTravelRoster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KhmerTravelDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        putenv('SEED_KHMER_DEMO=true');
        $_ENV['SEED_KHMER_DEMO'] = 'true';
        $_SERVER['SEED_KHMER_DEMO'] = 'true';
    }

    protected function tearDown(): void
    {
        putenv('SEED_KHMER_DEMO');
        unset($_ENV['SEED_KHMER_DEMO'], $_SERVER['SEED_KHMER_DEMO']);

        parent::tearDown();
    }

    public function test_roster_builder_produces_five_hundred_unique_handles(): void
    {
        $rows = KhmerTravelRoster::build();

        $this->assertCount(500, $rows);
        $this->assertCount(500, collect($rows)->pluck('handle')->unique());

        foreach ($rows as $row) {
            $this->assertMatchesRegularExpression('/^[a-zA-Z0-9._-]+$/', $row['handle']);
            $this->assertSame('https://www.tiktok.com/@'.$row['handle'], $row['tiktok_url']);
        }

        $counts = KhmerTravelRoster::sourceCounts($rows);
        $this->assertSame(30, $counts['public']);
        $this->assertSame(470, $counts['synthetic']);
    }

    public function test_khmer_travel_demo_seeder_seeds_portal_data(): void
    {
        User::factory()->operator()->create([
            'email' => 'operator@creator-operator.local',
            'operator_plan' => OperatorPlan::Starter,
        ]);

        $this->seed(KhmerTravelDemoSeeder::class);

        $this->assertSame(500, Creator::query()->count());
        $this->assertSame(500, WeeklyMetric::query()->count());
        $this->assertGreaterThanOrEqual(1500, PublishLogEntry::query()->count());
        $this->assertLessThanOrEqual(2500, PublishLogEntry::query()->count());

        $operator = User::query()->where('email', 'operator@creator-operator.local')->first();
        $this->assertSame(OperatorPlan::Demo, $operator?->operator_plan);
    }
}
