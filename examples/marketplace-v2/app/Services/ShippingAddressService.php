<?php

namespace App\Services;

use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShippingAddressService
{
    public function forUser(User $user)
    {
        return ShippingAddress::query()
            ->where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->orderBy('label')
            ->get();
    }

    public function defaultFor(User $user): ?ShippingAddress
    {
        return ShippingAddress::query()
            ->where('user_id', $user->id)
            ->where('is_default', true)
            ->first()
            ?? ShippingAddress::query()->where('user_id', $user->id)->first();
    }

    public function resolveForCheckout(User $user, ?int $addressId): ?ShippingAddress
    {
        if (! ShippingAddress::query()->where('user_id', $user->id)->exists()) {
            return null;
        }

        if ($addressId !== null) {
            $address = ShippingAddress::query()
                ->where('user_id', $user->id)
                ->whereKey($addressId)
                ->first();

            if ($address === null) {
                throw ValidationException::withMessages([
                    'shipping_address_id' => 'Select a valid shipping address.',
                ]);
            }

            return $address;
        }

        $default = $this->defaultFor($user);

        if ($default === null) {
            throw ValidationException::withMessages([
                'shipping_address_id' => 'Add a shipping address before checkout.',
            ]);
        }

        return $default;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): ShippingAddress
    {
        return DB::transaction(function () use ($user, $data) {
            if (! empty($data['is_default'])) {
                $this->clearDefault($user);
            }

            $address = ShippingAddress::query()->create([
                ...$data,
                'user_id' => $user->id,
            ]);

            if ($user->shippingAddresses()->count() === 1) {
                $address->update(['is_default' => true]);
            }

            return $address->fresh();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(ShippingAddress $address, array $data): ShippingAddress
    {
        return DB::transaction(function () use ($address, $data) {
            if (! empty($data['is_default'])) {
                $this->clearDefault($address->user);
            }

            $address->update($data);

            return $address->fresh();
        });
    }

    public function delete(ShippingAddress $address): void
    {
        DB::transaction(function () use ($address) {
            $wasDefault = $address->is_default;
            $user = $address->user;
            $address->delete();

            if ($wasDefault) {
                $user->shippingAddresses()->first()?->update(['is_default' => true]);
            }
        });
    }

    private function clearDefault(User $user): void
    {
        ShippingAddress::query()
            ->where('user_id', $user->id)
            ->update(['is_default' => false]);
    }
}
