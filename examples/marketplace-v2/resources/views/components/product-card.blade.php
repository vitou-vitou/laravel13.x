@props(['product'])

@php
    $minPrice = $product->variants->min('price_cents');
    $isNew = $product->created_at?->isAfter(now()->subDays(7));
@endphp

<a href="{{ route('catalog.show', $product) }}" class="store-card group flex flex-col">
    <div class="relative aspect-[4/3] overflow-hidden bg-stone-100">
        <img
            src="{{ $product->displayImageUrl() }}"
            alt="{{ $product->name }}"
            class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
            loading="lazy"
        />
        <div class="absolute left-2 top-2 flex flex-wrap gap-1 sm:left-3 sm:top-3">
            @if ($isNew)
                <span class="rounded-full bg-brand-600 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white sm:px-2.5 sm:py-1 sm:text-xs">
                    New
                </span>
            @endif
            @if ($minPrice !== null)
                <span class="rounded-full bg-white/90 px-2 py-0.5 text-[10px] font-semibold text-stone-800 backdrop-blur sm:px-2.5 sm:py-1 sm:text-xs">
                    from ${{ number_format($minPrice / 100, 2) }}
                </span>
            @endif
        </div>
    </div>
    <div class="flex flex-1 flex-col gap-0.5 p-3 sm:gap-1 sm:p-4">
        <p class="truncate text-[10px] font-medium uppercase tracking-wide text-brand-600 sm:text-xs">
            {{ $product->vendor?->store_name ?? 'Marketplace' }}
        </p>
        <h3 class="line-clamp-2 text-sm font-semibold text-stone-900 group-hover:text-brand-700 sm:text-base">
            {{ $product->name }}
        </h3>
        @if ($product->description)
            <p class="hidden line-clamp-2 text-sm text-stone-500 sm:block">{{ $product->description }}</p>
        @endif
    </div>
</a>
