<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ActionController extends Controller {
    public function index(Request $request) {
        $projectId = $request->project ?? DB::table('hive_projects')->value('id');
        $projects  = DB::table('hive_projects')->get();
        $project   = DB::table('hive_projects')->find($projectId);

        $q = DB::table('hive_actions')->where('project_id', $projectId);
        if (filled($request->status))   $q->where('status', $request->status);
        if (filled($request->priority)) $q->where('priority', $request->priority);
        if (filled($request->assignee)) $q->where('assignee', $request->assignee);
        if (filled($request->label))    $q->where('label', $request->label);
        $actions = $q->orderByRaw("CASE status WHEN 'in_progress' THEN 1 WHEN 'in_review' THEN 2 WHEN 'to_do' THEN 3 ELSE 4 END")
                     ->orderByRaw("CASE priority WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END")
                     ->get();

        $assignees = DB::table('hive_actions')->where('project_id', $projectId)->distinct()->pluck('assignee')->filter();
        $labels    = DB::table('hive_actions')->where('project_id', $projectId)->distinct()->pluck('label')->filter();

        $all = DB::table('hive_actions')->where('project_id', $projectId)->get();
        $stats = [
            'to_do'       => $all->where('status','to_do')->count(),
            'in_progress' => $all->where('status','in_progress')->count(),
            'in_review'   => $all->where('status','in_review')->count(),
            'completed'   => $all->where('status','completed')->count(),
            'total_time'  => $all->sum('time_logged'),
        ];

        // Analytics: per-assignee summary
        $byAssignee = $all->groupBy('assignee')->map(function($acts, $name) {
            return [
                'name'      => $name ?: 'Unassigned',
                'total'     => $acts->count(),
                'done'      => $acts->where('status','completed')->count(),
                'time'      => $acts->sum('time_logged'),
            ];
        })->sortByDesc('total')->values();

        return view('actions.index', compact('projects','project','actions','assignees','labels','stats','byAssignee'));
    }
}
