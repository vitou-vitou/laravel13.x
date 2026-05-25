<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClickUpSeeder extends Seeder {
    public function run(): void {
        $spaces = [['Engineering','⚙️'],['Design','🎨'],['Product','📋'],['Marketing','📣']];
        $statuses = ['open','in_progress','in_review','closed'];
        $priorities = ['urgent','high','normal','low'];
        $tags = ['bug','feature','improvement','docs','research','devops'];
        $members = ['Alice','Bob','Carol','David','Eve','Frank'];
        $tasks = [
            'Implement OAuth login','Fix memory leak in worker','Design onboarding flow',
            'Update API rate limits','Write release notes','Refactor auth module',
            'Add dark mode support','Performance profiling','SEO audit','A/B test CTA button',
            'Setup monitoring alerts','Database index optimization','Mobile push notifications',
            'Onboarding email sequence','Competitor analysis','User retention report',
            'Integrate Stripe webhooks','Load test API endpoints','Accessibility review','Launch checklist',
        ];

        foreach ($spaces as [$name, $icon]) {
            $sid = DB::table('spaces')->insertGetId(['name'=>$name,'icon'=>$icon,'created_at'=>now(),'updated_at'=>now()]);
            foreach (array_slice($tasks, 0, 10) as $i => $title) {
                DB::table('cu_tasks')->insert([
                    'space_id'      => $sid,
                    'title'         => $title,
                    'assignee'      => $members[array_rand($members)],
                    'status'        => $statuses[$i % 4],
                    'priority'      => $priorities[$i % 4],
                    'tag'           => $tags[$i % count($tags)],
                    'due_date'      => now()->addDays(rand(1,21))->toDateString(),
                    'time_estimate' => [30,60,90,120,180][rand(0,4)],
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }
    }
}
