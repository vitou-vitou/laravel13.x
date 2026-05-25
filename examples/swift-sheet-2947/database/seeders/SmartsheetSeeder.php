<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SmartsheetSeeder extends Seeder {
    public function run(): void {
        $sheets = [
            ['name'=>'Project Plan','icon'=>'📋','color'=>'bg-emerald-500'],
            ['name'=>'Budget Tracker','icon'=>'💰','color'=>'bg-green-600'],
            ['name'=>'Resource Allocation','icon'=>'👥','color'=>'bg-teal-500'],
        ];
        foreach ($sheets as $s) {
            DB::table('ss_sheets')->insert(array_merge($s, ['created_at'=>now(),'updated_at'=>now()]));
        }

        $s1 = DB::table('ss_sheets')->where('name','Project Plan')->value('id');
        $s2 = DB::table('ss_sheets')->where('name','Budget Tracker')->value('id');
        $s3 = DB::table('ss_sheets')->where('name','Resource Allocation')->value('id');

        $rows1 = [
            ['sheet_id'=>$s1,'task_name'=>'Project Kickoff','assigned_to'=>'Frank','status'=>'complete','priority'=>'high','start_date'=>'2026-04-01','end_date'=>'2026-04-03','duration'=>3,'percent_complete'=>100,'predecessors'=>null,'comments'=>'All stakeholders aligned','budget'=>5000,'actual_cost'=>4800,'row_order'=>1],
            ['sheet_id'=>$s1,'task_name'=>'Requirements Gathering','assigned_to'=>'Alice','status'=>'complete','priority'=>'high','start_date'=>'2026-04-04','end_date'=>'2026-04-14','duration'=>8,'percent_complete'=>100,'predecessors'=>'1','comments'=>null,'budget'=>8000,'actual_cost'=>7500,'row_order'=>2],
            ['sheet_id'=>$s1,'task_name'=>'System Architecture Design','assigned_to'=>'Bob','status'=>'complete','priority'=>'high','start_date'=>'2026-04-15','end_date'=>'2026-04-25','duration'=>9,'percent_complete'=>100,'predecessors'=>'2','comments'=>null,'budget'=>10000,'actual_cost'=>9200,'row_order'=>3],
            ['sheet_id'=>$s1,'task_name'=>'UI/UX Wireframes','assigned_to'=>'Dave','status'=>'complete','priority'=>'medium','start_date'=>'2026-04-15','end_date'=>'2026-04-30','duration'=>12,'percent_complete'=>100,'predecessors'=>'2','comments'=>null,'budget'=>6000,'actual_cost'=>5800,'row_order'=>4],
            ['sheet_id'=>$s1,'task_name'=>'Backend Development','assigned_to'=>'Bob','status'=>'in_progress','priority'=>'high','start_date'=>'2026-04-28','end_date'=>'2026-05-30','duration'=>25,'percent_complete'=>70,'predecessors'=>'3','comments'=>'On track','budget'=>40000,'actual_cost'=>28000,'row_order'=>5],
            ['sheet_id'=>$s1,'task_name'=>'Frontend Development','assigned_to'=>'Carol','status'=>'in_progress','priority'=>'high','start_date'=>'2026-05-01','end_date'=>'2026-05-30','duration'=>22,'percent_complete'=>55,'predecessors'=>'4','comments'=>null,'budget'=>30000,'actual_cost'=>16500,'row_order'=>6],
            ['sheet_id'=>$s1,'task_name'=>'Integration & API Testing','assigned_to'=>'Alice','status'=>'not_started','priority'=>'high','start_date'=>'2026-05-31','end_date'=>'2026-06-10','duration'=>9,'percent_complete'=>0,'predecessors'=>'5,6','comments'=>null,'budget'=>12000,'actual_cost'=>0,'row_order'=>7],
            ['sheet_id'=>$s1,'task_name'=>'User Acceptance Testing','assigned_to'=>'Frank','status'=>'not_started','priority'=>'medium','start_date'=>'2026-06-11','end_date'=>'2026-06-20','duration'=>8,'percent_complete'=>0,'predecessors'=>'7','comments'=>null,'budget'=>8000,'actual_cost'=>0,'row_order'=>8],
            ['sheet_id'=>$s1,'task_name'=>'Performance Optimization','assigned_to'=>'Bob','status'=>'not_started','priority'=>'medium','start_date'=>'2026-06-15','end_date'=>'2026-06-22','duration'=>6,'percent_complete'=>0,'predecessors'=>'7','comments'=>null,'budget'=>5000,'actual_cost'=>0,'row_order'=>9],
            ['sheet_id'=>$s1,'task_name'=>'Documentation','assigned_to'=>'Alice','status'=>'in_progress','priority'=>'low','start_date'=>'2026-05-20','end_date'=>'2026-06-20','duration'=>24,'percent_complete'=>30,'predecessors'=>null,'comments'=>null,'budget'=>4000,'actual_cost'=>1200,'row_order'=>10],
            ['sheet_id'=>$s1,'task_name'=>'Deployment to Production','assigned_to'=>'Bob','status'=>'not_started','priority'=>'critical','start_date'=>'2026-06-23','end_date'=>'2026-06-25','duration'=>3,'percent_complete'=>0,'predecessors'=>'8,9','comments'=>'Needs sign-off from Frank','budget'=>3000,'actual_cost'=>0,'row_order'=>11],
            ['sheet_id'=>$s1,'task_name'=>'Post-Launch Review','assigned_to'=>'Frank','status'=>'not_started','priority'=>'low','start_date'=>'2026-06-28','end_date'=>'2026-06-30','duration'=>3,'percent_complete'=>0,'predecessors'=>'11','comments'=>null,'budget'=>2000,'actual_cost'=>0,'row_order'=>12],
        ];

        $rows2 = [
            ['sheet_id'=>$s2,'task_name'=>'Q1 Personnel Costs','assigned_to'=>'Frank','status'=>'complete','priority'=>'high','start_date'=>'2026-01-01','end_date'=>'2026-03-31','duration'=>90,'percent_complete'=>100,'predecessors'=>null,'comments'=>null,'budget'=>120000,'actual_cost'=>118500,'row_order'=>1],
            ['sheet_id'=>$s2,'task_name'=>'Q2 Personnel Costs','assigned_to'=>'Frank','status'=>'in_progress','priority'=>'high','start_date'=>'2026-04-01','end_date'=>'2026-06-30','duration'=>91,'percent_complete'=>60,'predecessors'=>'1','comments'=>null,'budget'=>125000,'actual_cost'=>75000,'row_order'=>2],
            ['sheet_id'=>$s2,'task_name'=>'Software Licenses','assigned_to'=>'Alice','status'=>'in_progress','priority'=>'medium','start_date'=>'2026-01-01','end_date'=>'2026-12-31','duration'=>365,'percent_complete'=>42,'predecessors'=>null,'comments'=>'Annual renewals','budget'=>24000,'actual_cost'=>10000,'row_order'=>3],
            ['sheet_id'=>$s2,'task_name'=>'Cloud Infrastructure','assigned_to'=>'Bob','status'=>'in_progress','priority'=>'high','start_date'=>'2026-01-01','end_date'=>'2026-12-31','duration'=>365,'percent_complete'=>42,'predecessors'=>null,'comments'=>'AWS costs','budget'=>60000,'actual_cost'=>25000,'row_order'=>4],
            ['sheet_id'=>$s2,'task_name'=>'Marketing Budget','assigned_to'=>'Alice','status'=>'in_progress','priority'=>'medium','start_date'=>'2026-01-01','end_date'=>'2026-12-31','duration'=>365,'percent_complete'=>42,'predecessors'=>null,'comments'=>null,'budget'=>80000,'actual_cost'=>33600,'row_order'=>5],
        ];

        $rows3 = [
            ['sheet_id'=>$s3,'task_name'=>'Alice Chen - Backend','assigned_to'=>'Alice','status'=>'in_progress','priority'=>'high','start_date'=>'2026-05-01','end_date'=>'2026-06-30','duration'=>44,'percent_complete'=>50,'predecessors'=>null,'comments'=>'100% allocation','budget'=>null,'actual_cost'=>null,'row_order'=>1],
            ['sheet_id'=>$s3,'task_name'=>'Bob Martinez - DevOps','assigned_to'=>'Bob','status'=>'in_progress','priority'=>'high','start_date'=>'2026-05-01','end_date'=>'2026-06-30','duration'=>44,'percent_complete'=>50,'predecessors'=>null,'comments'=>'80% allocation','budget'=>null,'actual_cost'=>null,'row_order'=>2],
            ['sheet_id'=>$s3,'task_name'=>'Carol Kim - Frontend','assigned_to'=>'Carol','status'=>'in_progress','priority'=>'high','start_date'=>'2026-05-01','end_date'=>'2026-06-30','duration'=>44,'percent_complete'=>50,'predecessors'=>null,'comments'=>'100% allocation','budget'=>null,'actual_cost'=>null,'row_order'=>3],
            ['sheet_id'=>$s3,'task_name'=>'Dave Wilson - Design','assigned_to'=>'Dave','status'=>'in_progress','priority'=>'medium','start_date'=>'2026-05-01','end_date'=>'2026-05-31','duration'=>23,'percent_complete'=>85,'predecessors'=>null,'comments'=>'60% allocation','budget'=>null,'actual_cost'=>null,'row_order'=>4],
            ['sheet_id'=>$s3,'task_name'=>'Eve Johnson - QA','assigned_to'=>'Eve','status'=>'not_started','priority'=>'medium','start_date'=>'2026-06-01','end_date'=>'2026-06-30','duration'=>22,'percent_complete'=>0,'predecessors'=>null,'comments'=>'100% allocation from June','budget'=>null,'actual_cost'=>null,'row_order'=>5],
        ];

        foreach (array_merge($rows1, $rows2, $rows3) as $r) {
            DB::table('ss_rows')->insert(array_merge($r, ['created_at'=>now(),'updated_at'=>now()]));
        }
    }
}
