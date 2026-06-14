<x-app-layout>
    <div class="bg-stone-50">
        {{-- Hero --}}
        <section class="border-b border-stone-200/80 bg-white">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="max-w-2xl">
                    <p class="text-sm font-semibold uppercase tracking-wider text-brand-600">Marketplace v2</p>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-stone-900 sm:text-4xl">
                        Discover products from trusted vendors
                    </h1>
                    <p class="mt-3 text-lg text-stone-600">
                        Search, filter by category, and add variants to your cart — same flows, better storefront.
                    </p>
                </div>

                <form method="GET" action="{{ route('catalog.index') }}" class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="relative flex-1">
                        <label for="search" class="sr-only">Search products</label>
                        <input
                            id="search"
                            type="search"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="Search products…"
                            class="store-input pl-11"
                        />
                        <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                        </svg>
                    </div>
                    <button type="submit" class="btn-brand shrink-0">Search</button>
                </form>
            </div>
        </section>

        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            @if ($categories->isNotEmpty())
                <div class="mb-8 flex flex-wrap gap-2">
                    <a
                        href="{{ route('catalog.index', array_filter(['q' => request('q')])) }}"
                        class="chip {{ ! request('category') ? 'chip-active' : '' }}"
                    >
                        All
                    </a>
                    @foreach ($categories as $category)
                        <a
                            href="{{ route('catalog.index', array_filter(['category' => $category->slug, 'q' => request('q')])) }}"
                            class="chip {{ request('category') === $category->slug ? 'chip-active' : '' }}"
                        >
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            @if ($products->isEmpty())
                <div class="rounded-2xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center">
                    <p class="text-lg font-medium text-stone-900">No products found</p>
                    <p class="mt-1 text-stone-500">Try another search or category.</p>
                    <a href="{{ route('catalog.index') }}" class="btn-brand-outline mt-6 inline-flex">Clear filters</a>
                </div>
            @else
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($products as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>

                <div class="mt-10">
                    {{ $products->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
