<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class SheetController extends Controller {
    public function index(Request $request) {
        $sheetId = $request->sheet ?? DB::table('ss_sheets')->value('id');
        $sheets  = DB::table('ss_sheets')->get();
        $sheet   = DB::table('ss_sheets')->find($sheetId);

        $q = DB::table('ss_rows')->where('sheet_id', $sheetId);
        if (filled($request->status))      $q->where('status', $request->status);
        if (filled($request->priority))    $q->where('priority', $request->priority);
        if (filled($request->assigned_to)) $q->where('assigned_to', $request->assigned_to);
        $rows = $q->orderBy('row_order')->get();

        $assignees = DB::table('ss_rows')->where('sheet_id', $sheetId)->distinct()->pluck('assigned_to')->filter();

        $allRows = DB::table('ss_rows')->where('sheet_id', $sheetId)->get();
        $stats = [
            'total'        => $allRows->count(),
            'complete'     => $allRows->where('status','complete')->count(),
            'in_progress'  => $allRows->where('status','in_progress')->count(),
            'not_started'  => $allRows->where('status','not_started')->count(),
            'budget'       => $allRows->sum('budget'),
            'actual_cost'  => $allRows->sum('actual_cost'),
        ];

        return view('sheets.index', compact('sheets','sheet','rows','assignees','stats'));
    }
}
