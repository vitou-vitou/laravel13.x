<form method="POST" action="{{ $action }}" class="grid gap-3 sm:grid-cols-2">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    @php($address = $address ?? null)

    <div>
        <label class="block text-sm font-medium text-stone-700">Label</label>
        <input type="text" name="label" value="{{ old('label', $address?->label) }}" class="store-input mt-1" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-stone-700">Recipient name</label>
        <input type="text" name="name" value="{{ old('name', $address?->name) }}" class="store-input mt-1" required>
    </div>
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-stone-700">Address line 1</label>
        <input type="text" name="line1" value="{{ old('line1', $address?->line1) }}" class="store-input mt-1" required>
    </div>
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-stone-700">Address line 2</label>
        <input type="text" name="line2" value="{{ old('line2', $address?->line2) }}" class="store-input mt-1">
    </div>
    <div>
        <label class="block text-sm font-medium text-stone-700">City</label>
        <input type="text" name="city" value="{{ old('city', $address?->city) }}" class="store-input mt-1" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-stone-700">State / region</label>
        <input type="text" name="region" value="{{ old('region', $address?->region) }}" class="store-input mt-1" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-stone-700">Postal code</label>
        <input type="text" name="postal_code" value="{{ old('postal_code', $address?->postal_code) }}" class="store-input mt-1" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-stone-700">Country</label>
        <input type="text" name="country" value="{{ old('country', $address?->country ?? 'US') }}" maxlength="2" class="store-input mt-1 uppercase" required>
    </div>
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-stone-700">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $address?->phone) }}" class="store-input mt-1">
    </div>
    <div class="sm:col-span-2 flex items-center gap-2">
        <input type="hidden" name="is_default" value="0">
        <input type="checkbox" name="is_default" value="1" id="default-{{ $address?->id ?? 'new' }}" @checked(old('is_default', $address?->is_default))>
        <label for="default-{{ $address?->id ?? 'new' }}" class="text-sm text-stone-700">Default shipping address</label>
    </div>
    <div class="sm:col-span-2">
        <button type="submit" class="btn-brand">{{ $address ? 'Update address' : 'Save address' }}</button>
    </div>
</form>
