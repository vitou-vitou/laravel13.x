<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\Booking\SlotCalculator;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SlotController extends Controller
{
    public function __invoke(Request $request, Service $service, SlotCalculator $calculator): JsonResponse
    {
        $resource = $service->providerProfile->resources()->firstOrFail();
        $from = Carbon::parse($request->query('from', now()->toDateString()))->startOfDay();
        $to = Carbon::parse($request->query('to', now()->addDays(7)->toDateString()))->endOfDay();

        $slots = $calculator->availableStarts($service, $resource, $from, $to);

        return response()->json([
            'data' => $slots->map(fn ($s) => $s->toIso8601String())->all(),
        ]);
    }
}
