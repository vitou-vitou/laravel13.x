<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ProjectController extends Controller {
    public function index(Request $request) {
        $projectId = $request->project ?? DB::table('tw_projects')->value('id');
        $projects  = DB::table('tw_projects')->get();
        $project   = DB::table('tw_projects')->find($projectId);

        $milestones = DB::table('tw_milestones')->where('project_id', $projectId)->orderBy('due_date')->get();

        $q = DB::table('tw_tasks')->where('project_id', $projectId);
        if (filled($request->status))    $q->where('status', $request->status);
        if (filled($request->priority))  $q->where('priority', $request->priority);
        if (filled($request->assignee))  $q->where('assignee', $request->assignee);
        if (filled($request->milestone)) $q->where('milestone_id', $request->milestone);
        $tasks = $q->orderByRaw("CASE status WHEN 'in_progress' THEN 1 WHEN 'new' THEN 2 ELSE 3 END")
                   ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
                   ->get();

        $tasksByMilestone = $tasks->groupBy('milestone_id');
        $assignees = DB::table('tw_tasks')->where('project_id', $projectId)->distinct()->pluck('assignee')->filter();

        $stats = [
            'new'         => DB::table('tw_tasks')->where('project_id',$projectId)->where('status','new')->count(),
            'in_progress' => DB::table('tw_tasks')->where('project_id',$projectId)->where('status','in_progress')->count(),
            'completed'   => DB::table('tw_tasks')->where('project_id',$projectId)->where('status','completed')->count(),
            'milestones'  => $milestones->count(),
        ];

        return view('projects.index', compact('projects','project','milestones','tasks','tasksByMilestone','assignees','stats'));
    }
}
