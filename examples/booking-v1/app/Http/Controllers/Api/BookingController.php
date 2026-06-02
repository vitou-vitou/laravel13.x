<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Service;
use App\Services\Booking\BookingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class BookingController extends Controller
{
    public function hold(Request $request, Service $service, BookingService $bookings): JsonResponse
    {
        $validated = $request->validate([
            'starts_at' => ['required', 'date'],
        ]);

        try {
            $booking = $bookings->hold(
                $request->user(),
                $service,
                Carbon::parse($validated['starts_at']),
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => $booking], 201);
    }

    public function confirm(Request $request, Booking $booking, BookingService $bookings): JsonResponse
    {
        $this->authorize('confirm', $booking);

        try {
            $booking = $bookings->confirm($booking);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => $booking]);
    }

    public function cancel(Request $request, Booking $booking, BookingService $bookings): JsonResponse
    {
        $this->authorize('cancel', $booking);

        try {
            $booking = $bookings->cancel($booking, $request->user());
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => $booking]);
    }

    public function mine(Request $request): JsonResponse
    {
        $bookings = Booking::query()
            ->where('customer_id', $request->user()->id)
            ->with(['service', 'bookableResource'])
            ->orderBy('starts_at')
            ->get();

        return response()->json(['data' => $bookings]);
    }
}
