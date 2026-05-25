<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PageController extends Controller {
    public function index() {
        $first = DB::table('pages')->orderBy('sort_order')->value('id');
        return redirect()->route('pages.show', $first);
    }

    public function show($id, Request $request) {
        $pages = DB::table('pages')->orderBy('sort_order')->get();
        $page  = DB::table('pages')->find($id);

        $rows = collect();
        if ($page && $page->type === 'database') {
            $q = DB::table('db_rows')->where('page_id', $id);
            if (filled($request->status))   $q->where('status', $request->status);
            if (filled($request->assignee)) $q->where('assignee', $request->assignee);
            if (filled($request->priority)) $q->where('priority', $request->priority);
            $rows = $q->orderByRaw("CASE status WHEN 'in_progress' THEN 1 WHEN 'not_started' THEN 2 WHEN 'done' THEN 3 ELSE 4 END")->get();
        }

        $assignees = $page && $page->type === 'database'
            ? DB::table('db_rows')->where('page_id', $id)->distinct()->pluck('assignee')
            : collect();

        return view('pages.show', compact('pages', 'page', 'rows', 'assignees'));
    }
}
