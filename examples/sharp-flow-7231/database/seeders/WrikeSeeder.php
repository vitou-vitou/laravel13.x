<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WrikeSeeder extends Seeder {
    public function run(): void {
        $folders = [
            ['name'=>'Website Redesign','icon'=>'🌐','color'=>'bg-blue-500','sort_order'=>1],
            ['name'=>'Mobile App v2','icon'=>'📱','color'=>'bg-purple-500','sort_order'=>2],
            ['name'=>'Q2 Marketing','icon'=>'📣','color'=>'bg-pink-500','sort_order'=>3],
            ['name'=>'Infrastructure','icon'=>'🏗️','color'=>'bg-orange-500','sort_order'=>4],
        ];
        foreach ($folders as $f) {
            DB::table('wrike_folders')->insert(array_merge($f, ['created_at'=>now(),'updated_at'=>now()]));
        }

        $f1 = DB::table('wrike_folders')->where('name','Website Redesign')->value('id');
        $f2 = DB::table('wrike_folders')->where('name','Mobile App v2')->value('id');
        $f3 = DB::table('wrike_folders')->where('name','Q2 Marketing')->value('id');
        $f4 = DB::table('wrike_folders')->where('name','Infrastructure')->value('id');

        $tasks = [
            // Website Redesign
            ['folder_id'=>$f1,'title'=>'Homepage wireframes','status'=>'completed','importance'=>'high','assignee'=>'Dave','start_date'=>'2026-05-01','due_date'=>'2026-05-08','effort'=>16],
            ['folder_id'=>$f1,'title'=>'Design system components','status'=>'completed','importance'=>'high','assignee'=>'Dave','start_date'=>'2026-05-05','due_date'=>'2026-05-15','effort'=>24],
            ['folder_id'=>$f1,'title'=>'Homepage development','status'=>'active','importance'=>'high','assignee'=>'Carol','start_date'=>'2026-05-16','due_date'=>'2026-05-28','effort'=>32],
            ['folder_id'=>$f1,'title'=>'SEO meta tags & sitemap','status'=>'active','importance'=>'normal','assignee'=>'Alice','start_date'=>'2026-05-20','due_date'=>'2026-05-27','effort'=>8],
            ['folder_id'=>$f1,'title'=>'Cross-browser testing','status'=>'deferred','importance'=>'normal','assignee'=>'Carol','start_date'=>'2026-05-29','due_date'=>'2026-06-02','effort'=>12],
            ['folder_id'=>$f1,'title'=>'Launch & DNS cutover','status'=>'deferred','importance'=>'high','assignee'=>'Bob','start_date'=>'2026-06-03','due_date'=>'2026-06-05','effort'=>4],
            // Mobile App
            ['folder_id'=>$f2,'title'=>'Auth screens UI','status'=>'completed','importance'=>'high','assignee'=>'Carol','start_date'=>'2026-04-20','due_date'=>'2026-04-30','effort'=>20],
            ['folder_id'=>$f2,'title'=>'Push notification service','status'=>'active','importance'=>'high','assignee'=>'Bob','start_date'=>'2026-05-10','due_date'=>'2026-05-25','effort'=>24],
            ['folder_id'=>$f2,'title'=>'Offline data sync','status'=>'active','importance'=>'normal','assignee'=>'Alice','start_date'=>'2026-05-15','due_date'=>'2026-06-01','effort'=>40],
            ['folder_id'=>$f2,'title'=>'App Store submission','status'=>'deferred','importance'=>'high','assignee'=>'Frank','start_date'=>'2026-06-05','due_date'=>'2026-06-10','effort'=>8],
            // Marketing
            ['folder_id'=>$f3,'title'=>'Blog post: Product update','status'=>'completed','importance'=>'normal','assignee'=>'Alice','start_date'=>'2026-05-01','due_date'=>'2026-05-05','effort'=>6],
            ['folder_id'=>$f3,'title'=>'PPC campaign setup','status'=>'active','importance'=>'high','assignee'=>'Frank','start_date'=>'2026-05-15','due_date'=>'2026-05-22','effort'=>12],
            ['folder_id'=>$f3,'title'=>'Webinar preparation','status'=>'active','importance'=>'normal','assignee'=>'Alice','start_date'=>'2026-05-20','due_date'=>'2026-06-01','effort'=>16],
            // Infrastructure
            ['folder_id'=>$f4,'title'=>'Upgrade Kubernetes cluster','status'=>'active','importance'=>'high','assignee'=>'Bob','start_date'=>'2026-05-18','due_date'=>'2026-05-25','effort'=>20],
            ['folder_id'=>$f4,'title'=>'Set up monitoring alerts','status'=>'active','importance'=>'normal','assignee'=>'Bob','start_date'=>'2026-05-22','due_date'=>'2026-05-30','effort'=>8],
            ['folder_id'=>$f4,'title'=>'Database backup automation','status'=>'deferred','importance'=>'low','assignee'=>'Alice','start_date'=>'2026-06-01','due_date'=>'2026-06-07','effort'=>6],
        ];
        foreach ($tasks as $t) {
            DB::table('wrike_tasks')->insert(array_merge($t, ['description'=>null,'created_at'=>now(),'updated_at'=>now()]));
        }
    }
}
