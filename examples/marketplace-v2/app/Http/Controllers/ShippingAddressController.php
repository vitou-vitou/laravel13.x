<?php

namespace App\Http\Controllers;

use App\Models\ShippingAddress;
use App\Services\ShippingAddressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class ShippingAddressController extends Controller implements HasMiddleware
{
    public function __construct(private ShippingAddressService $addresses) {}

    public static function middleware(): array
    {
        return [new Middleware('auth')];
    }

    public function index(): View
    {
        return view('account.addresses.index', [
            'addresses' => $this->addresses->forUser(auth()->user()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $this->addresses->create(auth()->user(), $validated);

        return redirect()->route('account.addresses.index')->with('status', 'Address saved.');
    }

    public function update(Request $request, ShippingAddress $shippingAddress): RedirectResponse
    {
        $this->authorize('update', $shippingAddress);

        $validated = $this->validated($request);
        $this->addresses->update($shippingAddress, $validated);

        return redirect()->route('account.addresses.index')->with('status', 'Address updated.');
    }

    public function destroy(ShippingAddress $shippingAddress): RedirectResponse
    {
        $this->authorize('delete', $shippingAddress);

        $this->addresses->delete($shippingAddress);

        return redirect()->route('account.addresses.index')->with('status', 'Address removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:40'],
            'name' => ['required', 'string', 'max:120'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'region' => ['required', 'string', 'max:120'],
            'postal_code' => ['required', 'string', 'max:32'],
            'country' => ['required', 'string', 'size:2'],
            'phone' => ['nullable', 'string', 'max:40'],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        $data['is_default'] = $request->boolean('is_default');

        return $data;
    }
}
