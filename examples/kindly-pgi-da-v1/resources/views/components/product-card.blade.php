@props(['product'])

@php
    $minPrice = $product->variants->min('price_cents');
    $isNew = $product->created_at?->isAfter(now()->subDays(7));
    $vendorRating = $product->vendor?->rating_count > 0 ? (float) $product->vendor->rating_avg : null;
@endphp

<a href="{{ route('catalog.show', $product) }}" class="store-card catalog-feed-card group flex flex-col">
    <div class="relative aspect-square overflow-hidden bg-stone-100">
        <img
            src="{{ $product->displayImageUrl() }}"
            alt="{{ $product->name }}"
            class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]"
            loading="lazy"
        />
        @if ($isNew)
            <div class="absolute left-1.5 top-1.5 sm:left-2 sm:top-2">
                <span class="catalog-badge-new">New</span>
            </div>
        @endif
    </div>
    <div class="flex flex-1 flex-col gap-1 p-2 sm:gap-1.5 sm:p-3">
        <h3 class="line-clamp-2 text-xs font-medium leading-snug text-stone-900 group-hover:text-brand-700 sm:text-sm sm:font-semibold">
            {{ $product->name }}
        </h3>
        <div class="mt-auto flex items-end justify-between gap-2 pt-0.5">
            @if ($minPrice !== null)
                <p class="catalog-price">
                    <span class="text-[10px] font-semibold text-brand-600/90 sm:text-xs">$</span>{{ number_format($minPrice / 100, 2) }}
                </p>
            @endif
            @if ($vendorRating !== null)
                <p class="shrink-0 text-[10px] text-stone-500 sm:text-xs" aria-label="Vendor rating {{ number_format($vendorRating, 1) }}">
                    <span class="text-amber-500" aria-hidden="true">★</span> {{ number_format($vendorRating, 1) }}
                </p>
            @endif
        </div>
        <p class="truncate text-[10px] text-stone-500 sm:text-xs">
            {{ $product->vendor?->store_name ?? 'Marketplace' }}
        </p>
    </div>
</a>
