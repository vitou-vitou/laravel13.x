<x-app-layout>
    <div class="bg-stone-50">
        @if ($isHome)
            <section class="border-b border-stone-200/80 bg-white">
                <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 sm:py-12">
                    <div class="max-w-2xl">
                        <p class="text-sm font-semibold uppercase tracking-wider text-brand-600">Marketplace v2</p>
                        <h1 class="mt-2 text-2xl font-bold tracking-tight text-stone-900 sm:text-4xl">
                            Discover products from trusted vendors
                        </h1>
                        <p class="mt-2 text-base text-stone-600 sm:mt-3 sm:text-lg">
                            Filter by category and price, sort your way — mobile-friendly discovery.
                        </p>
                    </div>

                    <form method="GET" action="{{ route('catalog.index') }}" class="mt-6 flex flex-col gap-3 sm:mt-8 sm:flex-row sm:items-center">
                        <div class="relative flex-1">
                            <label for="search" class="sr-only">Search products</label>
                            <input
                                id="search"
                                type="search"
                                name="q"
                                value="{{ request('q') }}"
                                placeholder="Search products…"
                                class="store-input min-h-11 pl-11"
                            />
                            <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                            </svg>
                        </div>
                        <button type="submit" class="btn-brand min-h-11 shrink-0">Search</button>
                    </form>
                </div>
            </section>

            @if ($featuredCategories->isNotEmpty())
                <section class="border-b border-stone-200/80 bg-stone-50">
                    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                        <h2 class="text-lg font-semibold text-stone-900">Shop by category</h2>
                        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                            @foreach ($featuredCategories as $category)
                                <a
                                    href="{{ route('catalog.index', ['category' => $category->slug]) }}"
                                    class="store-card flex min-h-20 flex-col justify-center p-4 text-center"
                                >
                                    <span class="font-semibold text-stone-900">{{ $category->name }}</span>
                                    <span class="mt-1 text-xs text-stone-500">{{ $category->products_count }} items</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif

            @if ($recentlyViewed->isNotEmpty())
                <section class="border-b border-stone-200/80 bg-white">
                    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                        <h2 class="text-lg font-semibold text-stone-900">Recently viewed</h2>
                        <div class="mt-4 grid grid-cols-2 gap-3 sm:gap-6 lg:grid-cols-4 xl:grid-cols-4">
                            @foreach ($recentlyViewed as $product)
                                <x-product-card :product="$product" />
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
        @else
            <section class="border-b border-stone-200/80 bg-white">
                <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <h1 class="text-2xl font-bold tracking-tight text-stone-900 sm:text-3xl">Catalog</h1>
                    <form method="GET" action="{{ route('catalog.index') }}" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="relative flex-1">
                            <label for="search-catalog" class="sr-only">Search products</label>
                            <input id="search-catalog" type="search" name="q" value="{{ request('q') }}" placeholder="Search products…" class="store-input min-h-11 pl-11" />
                        </div>
                        <button type="submit" class="btn-brand min-h-11 shrink-0">Search</button>
                    </form>
                </div>
            </section>
        @endif

        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
            <form method="GET" action="{{ route('catalog.index') }}" class="store-panel mb-6 space-y-4 p-4 sm:p-5">
                @if (request('q'))
                    <input type="hidden" name="q" value="{{ request('q') }}">
                @endif

                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    @if ($categories->isNotEmpty())
                        <div class="flex-1">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-stone-500">Category</p>
                            <div class="flex flex-wrap gap-2">
                                <a
                                    href="{{ route('catalog.index', array_filter(['q' => request('q'), 'sort' => $sort, 'min_price' => $minPrice, 'max_price' => $maxPrice])) }}"
                                    class="chip min-h-10 {{ ! request('category') ? 'chip-active' : '' }}"
                                >
                                    All
                                </a>
                                @foreach ($categories as $category)
                                    <a
                                        href="{{ route('catalog.index', array_filter(['category' => $category->slug, 'q' => request('q'), 'sort' => $sort, 'min_price' => $minPrice, 'max_price' => $maxPrice])) }}"
                                        class="chip min-h-10 {{ request('category') === $category->slug ? 'chip-active' : '' }}"
                                    >
                                        {{ $category->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <div>
                            <label for="sort" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-stone-500">Sort</label>
                            <select id="sort" name="sort" class="store-input min-h-11 w-full sm:w-44" onchange="this.form.submit()">
                                <option value="newest" @selected($sort === 'newest')>Newest</option>
                                <option value="price_asc" @selected($sort === 'price_asc')>Price: low to high</option>
                                <option value="price_desc" @selected($sort === 'price_desc')>Price: high to low</option>
                            </select>
                        </div>
                        <div>
                            <label for="min_price" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-stone-500">Min $</label>
                            <input id="min_price" type="number" name="min_price" min="0" value="{{ $minPrice }}" placeholder="0" class="store-input min-h-11 w-full sm:w-24">
                        </div>
                        <div>
                            <label for="max_price" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-stone-500">Max $</label>
                            <input id="max_price" type="number" name="max_price" min="0" value="{{ $maxPrice }}" placeholder="Any" class="store-input min-h-11 w-full sm:w-24">
                        </div>
                        <button type="submit" class="btn-brand-outline min-h-11 shrink-0">Apply</button>
                    </div>
                </div>
            </form>

            @if ($products->isEmpty())
                <div class="rounded-2xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center">
                    <p class="text-lg font-medium text-stone-900">No products found</p>
                    <p class="mt-1 text-stone-500">Try another search, category, or price range.</p>
                    <a href="{{ route('catalog.index') }}" class="btn-brand-outline mt-6 inline-flex min-h-11 items-center">Clear filters</a>
                </div>
            @else
                <div class="catalog-grid grid grid-cols-2 gap-3 sm:gap-6 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($products as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>

                <div class="mt-8 sm:mt-10">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
