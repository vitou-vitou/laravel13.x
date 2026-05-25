<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeamworkSeeder extends Seeder {
    public function run(): void {
        $projects = [
            ['name'=>'Client Portal','icon'=>'🏢','color'=>'bg-teal-500','status'=>'active','company'=>'Acme Corp','start_date'=>'2026-04-01','due_date'=>'2026-06-30'],
            ['name'=>'Internal Tools','icon'=>'⚙️','color'=>'bg-blue-500','status'=>'active','company'=>'Internal','start_date'=>'2026-03-01','due_date'=>'2026-07-31'],
            ['name'=>'Brand Refresh','icon'=>'🎨','color'=>'bg-purple-500','status'=>'on_hold','company'=>'Design Co','start_date'=>'2026-05-01','due_date'=>'2026-08-01'],
        ];
        foreach ($projects as $p) {
            DB::table('tw_projects')->insert(array_merge($p, ['created_at'=>now(),'updated_at'=>now()]));
        }

        $p1 = DB::table('tw_projects')->where('name','Client Portal')->value('id');
        $p2 = DB::table('tw_projects')->where('name','Internal Tools')->value('id');

        $milestones = [
            ['project_id'=>$p1,'title'=>'Phase 1: Auth & Dashboard','due_date'=>'2026-05-15','completed'=>1],
            ['project_id'=>$p1,'title'=>'Phase 2: Reports & Exports','due_date'=>'2026-06-01','completed'=>0],
            ['project_id'=>$p1,'title'=>'Phase 3: Launch','due_date'=>'2026-06-30','completed'=>0],
            ['project_id'=>$p2,'title'=>'Admin Panel v1','due_date'=>'2026-04-30','completed'=>1],
            ['project_id'=>$p2,'title'=>'Analytics Dashboard','due_date'=>'2026-06-15','completed'=>0],
        ];
        foreach ($milestones as $m) {
            DB::table('tw_milestones')->insert(array_merge($m, ['created_at'=>now(),'updated_at'=>now()]));
        }

        $m1 = DB::table('tw_milestones')->where('title','Phase 1: Auth & Dashboard')->value('id');
        $m2 = DB::table('tw_milestones')->where('title','Phase 2: Reports & Exports')->value('id');
        $m3 = DB::table('tw_milestones')->where('title','Phase 3: Launch')->value('id');
        $m4 = DB::table('tw_milestones')->where('title','Admin Panel v1')->value('id');
        $m5 = DB::table('tw_milestones')->where('title','Analytics Dashboard')->value('id');

        $tasks = [
            // Client Portal - Phase 1 (completed)
            ['project_id'=>$p1,'milestone_id'=>$m1,'title'=>'Set up OAuth login','status'=>'completed','priority'=>'high','assignee'=>'Alice','due_date'=>'2026-05-05','estimated_minutes'=>240],
            ['project_id'=>$p1,'milestone_id'=>$m1,'title'=>'Dashboard layout','status'=>'completed','priority'=>'high','assignee'=>'Dave','due_date'=>'2026-05-10','estimated_minutes'=>360],
            ['project_id'=>$p1,'milestone_id'=>$m1,'title'=>'User roles & permissions','status'=>'completed','priority'=>'high','assignee'=>'Alice','due_date'=>'2026-05-12','estimated_minutes'=>300],
            // Client Portal - Phase 2
            ['project_id'=>$p1,'milestone_id'=>$m2,'title'=>'PDF export feature','status'=>'in_progress','priority'=>'high','assignee'=>'Bob','due_date'=>'2026-05-25','estimated_minutes'=>480],
            ['project_id'=>$p1,'milestone_id'=>$m2,'title'=>'CSV download for tables','status'=>'in_progress','priority'=>'medium','assignee'=>'Carol','due_date'=>'2026-05-28','estimated_minutes'=>180],
            ['project_id'=>$p1,'milestone_id'=>$m2,'title'=>'Scheduled email reports','status'=>'new','priority'=>'medium','assignee'=>'Bob','due_date'=>'2026-06-01','estimated_minutes'=>360],
            // Client Portal - Phase 3
            ['project_id'=>$p1,'milestone_id'=>$m3,'title'=>'Final QA testing','status'=>'new','priority'=>'high','assignee'=>'Carol','due_date'=>'2026-06-20','estimated_minutes'=>480],
            ['project_id'=>$p1,'milestone_id'=>$m3,'title'=>'Client training session','status'=>'new','priority'=>'medium','assignee'=>'Frank','due_date'=>'2026-06-25','estimated_minutes'=>120],
            ['project_id'=>$p1,'milestone_id'=>$m3,'title'=>'Go-live deployment','status'=>'new','priority'=>'high','assignee'=>'Alice','due_date'=>'2026-06-30','estimated_minutes'=>60],
            // Internal Tools
            ['project_id'=>$p2,'milestone_id'=>$m4,'title'=>'Admin user management','status'=>'completed','priority'=>'high','assignee'=>'Alice','due_date'=>'2026-04-20','estimated_minutes'=>300],
            ['project_id'=>$p2,'milestone_id'=>$m4,'title'=>'Activity log viewer','status'=>'completed','priority'=>'medium','assignee'=>'Bob','due_date'=>'2026-04-28','estimated_minutes'=>240],
            ['project_id'=>$p2,'milestone_id'=>$m5,'title'=>'KPI dashboard widgets','status'=>'in_progress','priority'=>'high','assignee'=>'Alice','due_date'=>'2026-06-10','estimated_minutes'=>600],
            ['project_id'=>$p2,'milestone_id'=>$m5,'title'=>'Chart integrations','status'=>'new','priority'=>'medium','assignee'=>'Carol','due_date'=>'2026-06-15','estimated_minutes'=>360],
        ];
        foreach ($tasks as $t) {
            DB::table('tw_tasks')->insert(array_merge($t, ['created_at'=>now(),'updated_at'=>now()]));
        }
    }
}
