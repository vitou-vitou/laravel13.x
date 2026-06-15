<?php

namespace Database\Seeders;

use App\Enums\OperatorPlan;
use App\Enums\UserRole;
use App\Models\Creator;
use App\Models\User;
use Database\Seeders\Support\KhmerTravelRoster;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class KhmerTravelDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! $this->seedFlagEnabled('SEED_KHMER_DEMO')) {
            $this->command?->warn('KhmerTravelDemoSeeder skipped — set SEED_KHMER_DEMO=true to run.');

            return;
        }

        $operator = $this->ensureOperator();
        $operator->update(['operator_plan' => OperatorPlan::Demo]);

        $roster = KhmerTravelRoster::build();
        $existingHandles = Creator::query()->pluck('handle')->flip();

        $toInsert = [];
        $now = now();

        foreach ($roster as $row) {
            if ($existingHandles->has($row['handle'])) {
                continue;
            }

            $toInsert[] = [
                'user_id' => null,
                'handle' => $row['handle'],
                'tiktok_url' => $row['tiktok_url'],
                'tier' => $row['tier'],
                'music_policy' => $row['music_policy'],
                'youtube_manager_email' => $row['youtube_manager_email'],
                'meta_manager_email' => $row['meta_manager_email'],
                'last_run_date' => $row['last_run_date'],
                'onboarding_notes' => $row['onboarding_notes'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($toInsert, 100) as $chunk) {
            DB::table('creators')->insert($chunk);
        }

        $creators = Creator::query()
            ->whereIn('handle', collect($roster)->pluck('handle'))
            ->get(['id', 'handle'])
            ->keyBy('handle');

        $publishRows = [];
        $metricRows = [];

        foreach ($roster as $row) {
            $creator = $creators->get($row['handle']);
            if ($creator === null) {
                continue;
            }

            $index = (int) $row['creator_index'];
            $logs = KhmerTravelRoster::publishLogRows($creator->id, $index, $row['handle']);
            $publishRows = array_merge($publishRows, $logs);
            $metricRows[] = KhmerTravelRoster::weeklyMetricRow(
                $creator->id,
                $index,
                KhmerTravelRoster::videoId($index, 1),
            );
        }

        foreach (array_chunk($publishRows, 200) as $chunk) {
            DB::table('publish_log_entries')->insert($chunk);
        }

        foreach (array_chunk($metricRows, 200) as $chunk) {
            DB::table('weekly_metrics')->insert($chunk);
        }

        $csvPath = dirname(base_path(), 2).'/docs/creator-commission/demo-khmer-travel-500-creators.csv';
        KhmerTravelRoster::exportCsv($roster, $csvPath);

        $counts = KhmerTravelRoster::sourceCounts($roster);
        $this->command?->info(sprintf(
            'Khmer travel demo: %d creators seeded (%d public_list, %d synthetic_demo). CSV: %s',
            count($toInsert),
            $counts['public'],
            $counts['synthetic'],
            $csvPath,
        ));
    }

    private function ensureOperator(): User
    {
        $operator = User::query()->where('email', 'operator@creator-operator.local')->first();

        if ($operator !== null) {
            return $operator;
        }

        return User::query()->create([
            'name' => 'Ops Operator',
            'email' => 'operator@creator-operator.local',
            'password' => Hash::make('password'),
            'role' => UserRole::Operator,
            'operator_plan' => OperatorPlan::Demo,
            'email_verified_at' => now(),
        ]);
    }

    private function seedFlagEnabled(string $key): bool
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        return filter_var($value ?: false, FILTER_VALIDATE_BOOLEAN);
    }
}
