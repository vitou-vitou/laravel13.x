@props(['product'])

<a href="{{ route('catalog.show', $product) }}" class="store-card group flex flex-col">
    <div class="relative aspect-[4/3] overflow-hidden bg-stone-100">
        <img
            src="{{ $product->displayImageUrl() }}"
            alt="{{ $product->name }}"
            class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
            loading="lazy"
        />
        @if ($product->variants->isNotEmpty())
            <span class="absolute left-3 top-3 rounded-full bg-white/90 px-2.5 py-1 text-xs font-semibold text-stone-800 backdrop-blur">
                from ${{ number_format($product->variants->min('price_cents') / 100, 2) }}
            </span>
        @endif
    </div>
    <div class="flex flex-1 flex-col gap-1 p-4">
        <p class="text-xs font-medium uppercase tracking-wide text-brand-600">
            {{ $product->vendor?->store_name ?? 'Marketplace' }}
        </p>
        <h3 class="line-clamp-2 text-base font-semibold text-stone-900 group-hover:text-brand-700">
            {{ $product->name }}
        </h3>
        @if ($product->description)
            <p class="line-clamp-2 text-sm text-stone-500">{{ $product->description }}</p>
        @endif
    </div>
</a>
