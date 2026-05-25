<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class SpaceController extends Controller {
    public function index(Request $request) {
        $spaceId = $request->space ?? DB::table('spaces')->value('id');
        $spaces  = DB::table('spaces')->get();
        $space   = DB::table('spaces')->find($spaceId);

        $query = DB::table('cu_tasks')->where('space_id', $spaceId);
        if (filled($request->status))   $query->where('status', $request->status);
        if (filled($request->priority)) $query->where('priority', $request->priority);
        if (filled($request->tag))      $query->where('tag', $request->tag);
        if (filled($request->assignee)) $query->where('assignee', $request->assignee);

        $tasks     = $query->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'normal' THEN 3 ELSE 4 END")->get();
        $assignees = DB::table('cu_tasks')->where('space_id', $spaceId)->distinct()->pluck('assignee');
        $tags      = DB::table('cu_tasks')->where('space_id', $spaceId)->distinct()->pluck('tag');

        $stats = [
            'open'        => DB::table('cu_tasks')->where('space_id',$spaceId)->where('status','open')->count(),
            'in_progress' => DB::table('cu_tasks')->where('space_id',$spaceId)->where('status','in_progress')->count(),
            'closed'      => DB::table('cu_tasks')->where('space_id',$spaceId)->where('status','closed')->count(),
        ];

        return view('spaces.index', compact('spaces','space','tasks','assignees','tags','stats'));
    }
}
