<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MondaySeeder extends Seeder {
    public function run(): void {
        $boards = [
            ['name'=>'Product Roadmap','icon'=>'🗺️','color'=>'bg-indigo-500'],
            ['name'=>'Marketing Campaigns','icon'=>'📣','color'=>'bg-pink-500'],
            ['name'=>'Engineering Sprint','icon'=>'⚙️','color'=>'bg-blue-600'],
        ];
        foreach ($boards as $b) {
            DB::table('mon_boards')->insert(array_merge($b, ['created_at'=>now(),'updated_at'=>now()]));
        }

        $b1 = DB::table('mon_boards')->where('name','Product Roadmap')->value('id');

        $groups = [
            ['board_id'=>$b1,'name'=>'Q2 2026','color'=>'bg-green-500','sort_order'=>1],
            ['board_id'=>$b1,'name'=>'Q3 2026','color'=>'bg-yellow-500','sort_order'=>2],
            ['board_id'=>$b1,'name'=>'Backlog','color'=>'bg-gray-400','sort_order'=>3],
        ];
        foreach ($groups as $g) {
            DB::table('mon_groups')->insert(array_merge($g, ['created_at'=>now(),'updated_at'=>now()]));
        }

        $g1 = DB::table('mon_groups')->where('name','Q2 2026')->value('id');
        $g2 = DB::table('mon_groups')->where('name','Q3 2026')->value('id');
        $g3 = DB::table('mon_groups')->where('name','Backlog')->value('id');

        $items = [
            // Q2
            ['group_id'=>$g1,'board_id'=>$b1,'title'=>'Launch dashboard v2','status'=>'done','assignee'=>'Alice','due_date'=>'2026-05-15','priority'=>'high','progress'=>100],
            ['group_id'=>$g1,'board_id'=>$b1,'title'=>'API integration with Slack','status'=>'done','assignee'=>'Bob','due_date'=>'2026-05-20','priority'=>'high','progress'=>100],
            ['group_id'=>$g1,'board_id'=>$b1,'title'=>'Mobile push notifications','status'=>'working_on_it','assignee'=>'Carol','due_date'=>'2026-05-28','priority'=>'critical','progress'=>65],
            ['group_id'=>$g1,'board_id'=>$b1,'title'=>'Onboarding flow redesign','status'=>'working_on_it','assignee'=>'Dave','due_date'=>'2026-05-30','priority'=>'high','progress'=>40],
            ['group_id'=>$g1,'board_id'=>$b1,'title'=>'Analytics dashboard','status'=>'stuck','assignee'=>'Eve','due_date'=>'2026-05-25','priority'=>'medium','progress'=>20],
            ['group_id'=>$g1,'board_id'=>$b1,'title'=>'Bug: File upload timeout','status'=>'working_on_it','assignee'=>'Alice','due_date'=>'2026-05-26','priority'=>'critical','progress'=>80],
            // Q3
            ['group_id'=>$g2,'board_id'=>$b1,'title'=>'AI-powered suggestions','status'=>'not_started','assignee'=>'Bob','due_date'=>'2026-07-15','priority'=>'high','progress'=>0],
            ['group_id'=>$g2,'board_id'=>$b1,'title'=>'Enterprise SSO','status'=>'not_started','assignee'=>'Alice','due_date'=>'2026-08-01','priority'=>'critical','progress'=>0],
            ['group_id'=>$g2,'board_id'=>$b1,'title'=>'White-label solution','status'=>'not_started','assignee'=>null,'due_date'=>'2026-08-15','priority'=>'medium','progress'=>0],
            // Backlog
            ['group_id'=>$g3,'board_id'=>$b1,'title'=>'Dark mode support','status'=>'not_started','assignee'=>null,'due_date'=>null,'priority'=>'low','progress'=>0],
            ['group_id'=>$g3,'board_id'=>$b1,'title'=>'Offline mode','status'=>'not_started','assignee'=>null,'due_date'=>null,'priority'=>'medium','progress'=>0],
            ['group_id'=>$g3,'board_id'=>$b1,'title'=>'Custom domain support','status'=>'not_started','assignee'=>null,'due_date'=>null,'priority'=>'low','progress'=>0],
        ];
        foreach ($items as $i) {
            DB::table('mon_items')->insert(array_merge($i, ['timeline_start'=>null,'timeline_end'=>null,'created_at'=>now(),'updated_at'=>now()]));
        }
    }
}
