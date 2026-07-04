<?php

namespace App\Http\Controllers\Privacy;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GdprExportController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $user = auth()->user();

        $payload = [
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
                'created_at' => $user->created_at?->toIso8601String(),
            ],
            'orders' => $user->orders()
                ->with(['groups.lines', 'payment'])
                ->get()
                ->map(fn ($order) => [
                    'id' => $order->id,
                    'status' => $order->status->value,
                    'total_cents' => $order->total_cents,
                    'paid_at' => $order->paid_at?->toIso8601String(),
                    'groups' => $order->groups->map(fn ($g) => [
                        'vendor_id' => $g->vendor_id,
                        'subtotal_cents' => $g->subtotal_cents,
                        'status' => $g->status->value,
                    ]),
                ]),
        ];

        return response()->json($payload, 200, [
            'Content-Disposition' => 'attachment; filename="gdpr-export-'.$user->id.'.json"',
        ]);
    }
}
