<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HiveSeeder extends Seeder {
    public function run(): void {
        $projects = [
            ['name'=>'Product Launch','icon'=>'🚀','color'=>'bg-amber-500'],
            ['name'=>'Engineering','icon'=>'⚙️','color'=>'bg-blue-500'],
            ['name'=>'Marketing','icon'=>'📣','color'=>'bg-pink-500'],
            ['name'=>'Design','icon'=>'🎨','color'=>'bg-purple-500'],
        ];
        foreach ($projects as $p) {
            DB::table('hive_projects')->insert(array_merge($p, ['created_at'=>now(),'updated_at'=>now()]));
        }

        $p1 = DB::table('hive_projects')->where('name','Product Launch')->value('id');
        $p2 = DB::table('hive_projects')->where('name','Engineering')->value('id');
        $p3 = DB::table('hive_projects')->where('name','Marketing')->value('id');

        $actions = [
            // Product Launch
            ['project_id'=>$p1,'title'=>'Finalize feature set for v1.0','status'=>'completed','priority'=>'critical','assignee'=>'Frank','due_date'=>'2026-05-01','label'=>'strategy','time_logged'=>480],
            ['project_id'=>$p1,'title'=>'Write launch blog post','status'=>'completed','priority'=>'high','assignee'=>'Alice','due_date'=>'2026-05-10','label'=>'content','time_logged'=>240],
            ['project_id'=>$p1,'title'=>'Set up landing page','status'=>'in_progress','priority'=>'high','assignee'=>'Carol','due_date'=>'2026-05-25','label'=>'web','time_logged'=>180],
            ['project_id'=>$p1,'title'=>'Email campaign setup','status'=>'in_progress','priority'=>'medium','assignee'=>'Alice','due_date'=>'2026-05-27','label'=>'marketing','time_logged'=>90],
            ['project_id'=>$p1,'title'=>'Social media scheduling','status'=>'to_do','priority'=>'medium','assignee'=>'Alice','due_date'=>'2026-05-28','label'=>'marketing','time_logged'=>0],
            ['project_id'=>$p1,'title'=>'Press release draft','status'=>'to_do','priority'=>'high','assignee'=>'Frank','due_date'=>'2026-05-25','label'=>'pr','time_logged'=>0],
            ['project_id'=>$p1,'title'=>'Demo video recording','status'=>'in_review','priority'=>'high','assignee'=>'Dave','due_date'=>'2026-05-24','label'=>'content','time_logged'=>360],
            // Engineering
            ['project_id'=>$p2,'title'=>'API rate limiting','status'=>'completed','priority'=>'high','assignee'=>'Bob','due_date'=>'2026-05-15','label'=>'backend','time_logged'=>300],
            ['project_id'=>$p2,'title'=>'WebSocket notifications','status'=>'in_progress','priority'=>'medium','assignee'=>'Bob','due_date'=>'2026-05-28','label'=>'backend','time_logged'=>240],
            ['project_id'=>$p2,'title'=>'Mobile responsive fixes','status'=>'in_progress','priority'=>'high','assignee'=>'Carol','due_date'=>'2026-05-26','label'=>'frontend','time_logged'=>120],
            ['project_id'=>$p2,'title'=>'Performance audit','status'=>'to_do','priority'=>'medium','assignee'=>'Alice','due_date'=>'2026-06-01','label'=>'performance','time_logged'=>0],
            ['project_id'=>$p2,'title'=>'Security penetration test','status'=>'to_do','priority'=>'critical','assignee'=>'Bob','due_date'=>'2026-06-05','label'=>'security','time_logged'=>0],
            // Marketing
            ['project_id'=>$p3,'title'=>'Q2 content calendar','status'=>'completed','priority'=>'high','assignee'=>'Alice','due_date'=>'2026-05-01','label'=>'planning','time_logged'=>120],
            ['project_id'=>$p3,'title'=>'LinkedIn ad campaign','status'=>'in_progress','priority'=>'high','assignee'=>'Frank','due_date'=>'2026-05-28','label'=>'ads','time_logged'=>180],
            ['project_id'=>$p3,'title'=>'Case study interviews','status'=>'in_review','priority'=>'medium','assignee'=>'Alice','due_date'=>'2026-05-27','label'=>'content','time_logged'=>240],
        ];
        foreach ($actions as $a) {
            DB::table('hive_actions')->insert(array_merge($a, ['created_at'=>now(),'updated_at'=>now()]));
        }
    }
}
