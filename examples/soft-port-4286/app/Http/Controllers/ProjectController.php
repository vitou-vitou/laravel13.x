<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ProjectController extends Controller {
    public function index() {
        $projects = DB::table('projects')
            ->leftJoin('tasks', 'projects.id', '=', 'tasks.project_id')
            ->selectRaw('projects.*, COUNT(tasks.id) as task_count, SUM(tasks.status = "done") as done_count')
            ->groupBy('projects.id')
            ->get();
        return view('projects.index', compact('projects'));
    }

    public function show($id, Request $request) {
        $project = DB::table('projects')->find($id);
        $query = DB::table('tasks')->where('project_id', $id);
        if (filled($request->status)) $query->where('status', $request->status);
        if (filled($request->priority)) $query->where('priority', $request->priority);
        if (filled($request->assignee)) $query->where('assignee', $request->assignee);
        $tasks = $query->orderByRaw("CASE status WHEN 'in_progress' THEN 1 WHEN 'review' THEN 2 WHEN 'todo' THEN 3 ELSE 4 END")->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")->get();
        $assignees = DB::table('tasks')->where('project_id', $id)->distinct()->pluck('assignee');
        return view('projects.show', compact('project', 'tasks', 'assignees'));
    }
}
