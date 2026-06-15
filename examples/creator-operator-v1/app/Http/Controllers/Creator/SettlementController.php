<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SettlementController extends Controller
{
    public function index(Request $request): View
    {
        $creator = $request->user()->creatorProfile;
        abort_if($creator === null, 403);

        $settlements = $creator->monthlySettlements()->latest('period_start')->get();

        return view('creator.settlement.index', compact('creator', 'settlements'));
    }
}
