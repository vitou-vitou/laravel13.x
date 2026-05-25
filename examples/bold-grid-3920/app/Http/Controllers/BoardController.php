<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class BoardController extends Controller {
    public function index(Request $request) {
        $boardId = $request->board ?? DB::table('mon_boards')->value('id');
        $boards  = DB::table('mon_boards')->get();
        $board   = DB::table('mon_boards')->find($boardId);
        $groups  = DB::table('mon_groups')->where('board_id', $boardId)->orderBy('sort_order')->get();

        $allItems = DB::table('mon_items')->where('board_id', $boardId);
        if (filled($request->status))   $allItems->where('status', $request->status);
        if (filled($request->assignee)) $allItems->where('assignee', $request->assignee);
        $allItems = $allItems->get()->groupBy('group_id');

        $assignees = DB::table('mon_items')->where('board_id', $boardId)->distinct()->pluck('assignee')->filter();

        $stats = [
            'done'           => DB::table('mon_items')->where('board_id',$boardId)->where('status','done')->count(),
            'working_on_it'  => DB::table('mon_items')->where('board_id',$boardId)->where('status','working_on_it')->count(),
            'stuck'          => DB::table('mon_items')->where('board_id',$boardId)->where('status','stuck')->count(),
            'not_started'    => DB::table('mon_items')->where('board_id',$boardId)->where('status','not_started')->count(),
        ];

        return view('boards.index', compact('boards','board','groups','allItems','assignees','stats'));
    }
}
