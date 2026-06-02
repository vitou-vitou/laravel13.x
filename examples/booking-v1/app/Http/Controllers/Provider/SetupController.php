<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\AvailabilityRule;
use App\Models\BookableResource;
use App\Models\ProviderProfile;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SetupController extends Controller
{
    public function storeService(Request $request): JsonResponse
    {
        $profile = $this->profile($request);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'resource_name' => ['required', 'string', 'max:255'],
            'day_of_week' => ['required', 'integer', 'min:0', 'max:6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        $resource = BookableResource::query()->firstOrCreate(
            ['provider_profile_id' => $profile->id, 'name' => $validated['resource_name']],
        );

        AvailabilityRule::query()->updateOrCreate(
            [
                'bookable_resource_id' => $resource->id,
                'day_of_week' => $validated['day_of_week'],
            ],
            [
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
            ],
        );

        $service = Service::query()->create([
            'provider_profile_id' => $profile->id,
            'name' => $validated['name'],
            'duration_minutes' => $validated['duration_minutes'],
        ]);

        return response()->json(['data' => ['service' => $service, 'resource' => $resource]], 201);
    }

    private function profile(Request $request): ProviderProfile
    {
        return ProviderProfile::query()->firstOrCreate(
            ['user_id' => $request->user()->id],
            ['business_name' => $request->user()->name.' Business', 'timezone' => 'UTC'],
        );
    }
}
