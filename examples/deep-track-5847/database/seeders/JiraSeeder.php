<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JiraSeeder extends Seeder {
    public function run(): void {
        $projects = [
            ['name'=>'Platform Core','key'=>'PLAT','icon'=>'⚡','type'=>'scrum'],
            ['name'=>'Mobile App','key'=>'MOB','icon'=>'📱','type'=>'scrum'],
            ['name'=>'Design System','key'=>'DS','icon'=>'🎨','type'=>'kanban'],
        ];
        foreach ($projects as $p) {
            DB::table('jira_projects')->insert(array_merge($p, ['created_at'=>now(),'updated_at'=>now()]));
        }

        $proj1 = DB::table('jira_projects')->where('key','PLAT')->value('id');

        $sprints = [
            ['project_id'=>$proj1,'name'=>'Sprint 12','status'=>'completed','start_date'=>'2026-04-28','end_date'=>'2026-05-09'],
            ['project_id'=>$proj1,'name'=>'Sprint 13','status'=>'active','start_date'=>'2026-05-12','end_date'=>'2026-05-23'],
            ['project_id'=>$proj1,'name'=>'Sprint 14','status'=>'planning','start_date'=>'2026-05-26','end_date'=>'2026-06-06'],
        ];
        foreach ($sprints as $s) {
            DB::table('jira_sprints')->insert(array_merge($s, ['created_at'=>now(),'updated_at'=>now()]));
        }

        $sprint12 = DB::table('jira_sprints')->where('name','Sprint 12')->value('id');
        $sprint13 = DB::table('jira_sprints')->where('name','Sprint 13')->value('id');

        $issues = [
            // Sprint 13 (active)
            ['project_id'=>$proj1,'sprint_id'=>$sprint13,'key'=>'PLAT-45','title'=>'Implement OAuth2 login flow','type'=>'story','status'=>'in_progress','priority'=>'high','assignee'=>'Alice','reporter'=>'Frank','story_points'=>8,'due_date'=>'2026-05-22'],
            ['project_id'=>$proj1,'sprint_id'=>$sprint13,'key'=>'PLAT-46','title'=>'Fix session timeout bug','type'=>'bug','status'=>'in_review','priority'=>'highest','assignee'=>'Bob','reporter'=>'Alice','story_points'=>3,'due_date'=>'2026-05-20'],
            ['project_id'=>$proj1,'sprint_id'=>$sprint13,'key'=>'PLAT-47','title'=>'Add rate limiting to API','type'=>'task','status'=>'todo','priority'=>'medium','assignee'=>'Carol','reporter'=>'Frank','story_points'=>5,'due_date'=>'2026-05-23'],
            ['project_id'=>$proj1,'sprint_id'=>$sprint13,'key'=>'PLAT-48','title'=>'Database query optimization','type'=>'task','status'=>'in_progress','priority'=>'high','assignee'=>'Alice','reporter'=>'Bob','story_points'=>5,'due_date'=>'2026-05-21'],
            ['project_id'=>$proj1,'sprint_id'=>$sprint13,'key'=>'PLAT-49','title'=>'Update user profile endpoint','type'=>'story','status'=>'done','priority'=>'medium','assignee'=>'Bob','reporter'=>'Frank','story_points'=>3,'due_date'=>'2026-05-19'],
            ['project_id'=>$proj1,'sprint_id'=>$sprint13,'key'=>'PLAT-50','title'=>'Write integration tests for auth','type'=>'task','status'=>'todo','priority'=>'low','assignee'=>'Carol','reporter'=>'Alice','story_points'=>2,'due_date'=>'2026-05-23'],
            // Sprint 14 (planning — backlog)
            ['project_id'=>$proj1,'sprint_id'=>$sprint14=null,'key'=>'PLAT-51','title'=>'Webhook delivery system','type'=>'epic','status'=>'todo','priority'=>'high','assignee'=>null,'reporter'=>'Frank','story_points'=>13,'due_date'=>null],
            ['project_id'=>$proj1,'sprint_id'=>null,'key'=>'PLAT-52','title'=>'Audit log viewer','type'=>'story','status'=>'todo','priority'=>'medium','assignee'=>null,'reporter'=>'Frank','story_points'=>8,'due_date'=>null],
            ['project_id'=>$proj1,'sprint_id'=>null,'key'=>'PLAT-53','title'=>'CSV export for reports','type'=>'story','status'=>'todo','priority'=>'low','assignee'=>null,'reporter'=>'Alice','story_points'=>3,'due_date'=>null],
            ['project_id'=>$proj1,'sprint_id'=>null,'key'=>'PLAT-54','title'=>'SSO SAML integration','type'=>'epic','status'=>'todo','priority'=>'highest','assignee'=>null,'reporter'=>'Frank','story_points'=>21,'due_date'=>null],
            // Sprint 12 (completed)
            ['project_id'=>$proj1,'sprint_id'=>$sprint12,'key'=>'PLAT-41','title'=>'Redesign settings page','type'=>'story','status'=>'done','priority'=>'medium','assignee'=>'Carol','reporter'=>'Dave','story_points'=>5,'due_date'=>'2026-05-09'],
            ['project_id'=>$proj1,'sprint_id'=>$sprint12,'key'=>'PLAT-42','title'=>'Fix CORS headers bug','type'=>'bug','status'=>'done','priority'=>'high','assignee'=>'Alice','reporter'=>'Bob','story_points'=>2,'due_date'=>'2026-05-07'],
            ['project_id'=>$proj1,'sprint_id'=>$sprint12,'key'=>'PLAT-43','title'=>'Add pagination to list endpoints','type'=>'task','status'=>'done','priority'=>'medium','assignee'=>'Bob','reporter'=>'Frank','story_points'=>3,'due_date'=>'2026-05-09'],
            ['project_id'=>$proj1,'sprint_id'=>$sprint12,'key'=>'PLAT-44','title'=>'Upgrade PHP to 8.3','type'=>'task','status'=>'done','priority'=>'medium','assignee'=>'Alice','reporter'=>'Alice','story_points'=>2,'due_date'=>'2026-05-08'],
        ];
        foreach ($issues as $i) {
            DB::table('jira_issues')->insert(array_merge($i, ['created_at'=>now(),'updated_at'=>now()]));
        }
    }
}
