<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class IssueController extends Controller {
    public function index(Request $request) {
        $projectId = $request->project ?? DB::table('jira_projects')->value('id');
        $projects  = DB::table('jira_projects')->get();
        $project   = DB::table('jira_projects')->find($projectId);

        $view = $request->view ?? 'board';

        $sprints = DB::table('jira_sprints')->where('project_id', $projectId)->orderBy('status')->get();
        $activeSprint = $sprints->firstWhere('status', 'active');

        // Board view: active sprint issues
        $boardIssues = collect();
        if ($activeSprint) {
            $q = DB::table('jira_issues')->where('sprint_id', $activeSprint->id);
            if (filled($request->type))     $q->where('type', $request->type);
            if (filled($request->assignee)) $q->where('assignee', $request->assignee);
            $boardIssues = $q->get();
        }

        // Backlog: issues without sprint
        $backlog = DB::table('jira_issues')
            ->where('project_id', $projectId)
            ->whereNull('sprint_id')
            ->get();

        $assignees = DB::table('jira_issues')->where('project_id', $projectId)->distinct()->pluck('assignee')->filter();

        $stats = [
            'todo'        => DB::table('jira_issues')->where('project_id',$projectId)->where('status','todo')->count(),
            'in_progress' => DB::table('jira_issues')->where('project_id',$projectId)->where('status','in_progress')->count(),
            'in_review'   => DB::table('jira_issues')->where('project_id',$projectId)->where('status','in_review')->count(),
            'done'        => DB::table('jira_issues')->where('project_id',$projectId)->where('status','done')->count(),
        ];

        return view('issues.index', compact('projects','project','view','sprints','activeSprint','boardIssues','backlog','assignees','stats'));
    }
}
