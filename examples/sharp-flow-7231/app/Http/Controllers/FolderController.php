<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class FolderController extends Controller {
    public function index(Request $request) {
        $folderId = $request->folder ?? DB::table('wrike_folders')->value('id');
        $folders  = DB::table('wrike_folders')->orderBy('sort_order')->get();
        $folder   = DB::table('wrike_folders')->find($folderId);

        $q = DB::table('wrike_tasks')->where('folder_id', $folderId);
        if (filled($request->status))     $q->where('status', $request->status);
        if (filled($request->importance)) $q->where('importance', $request->importance);
        if (filled($request->assignee))   $q->where('assignee', $request->assignee);
        $tasks = $q->orderByRaw("CASE status WHEN 'active' THEN 1 WHEN 'deferred' THEN 2 WHEN 'completed' THEN 3 ELSE 4 END")
                   ->orderByRaw("CASE importance WHEN 'high' THEN 1 WHEN 'normal' THEN 2 ELSE 3 END")
                   ->get();

        $assignees = DB::table('wrike_tasks')->where('folder_id', $folderId)->distinct()->pluck('assignee')->filter();

        $stats = [
            'active'    => DB::table('wrike_tasks')->where('folder_id',$folderId)->where('status','active')->count(),
            'completed' => DB::table('wrike_tasks')->where('folder_id',$folderId)->where('status','completed')->count(),
            'deferred'  => DB::table('wrike_tasks')->where('folder_id',$folderId)->where('status','deferred')->count(),
        ];

        return view('folders.index', compact('folders','folder','tasks','assignees','stats'));
    }
}
