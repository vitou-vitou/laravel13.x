<x-app-layout>
    <div class="bg-stone-50 pb-20 sm:pb-10">
        @if ($isHome)
            <section class="border-b border-stone-200/80 bg-white">
                <div class="mx-auto max-w-7xl px-3 py-5 sm:px-6 sm:py-10 lg:px-8">
                    <div class="max-w-2xl">
                        <p class="text-xs font-semibold uppercase tracking-wider text-brand-600 sm:text-sm">Marketplace v2</p>
                        <h1 class="mt-1 text-xl font-bold tracking-tight text-stone-900 sm:mt-2 sm:text-4xl">
                            Discover products from trusted vendors
                        </h1>
                        <p class="mt-1 text-sm text-stone-600 sm:mt-3 sm:text-lg">
                            Dense mobile feed, full filters on desktop — Taobao-tier browsing without the clone.
                        </p>
                    </div>

                    <form method="GET" action="{{ route('catalog.index') }}" class="mt-4 flex gap-2 sm:mt-8">
                        <div class="relative min-w-0 flex-1">
                            <label for="search" class="sr-only">Search products</label>
                            <input
                                id="search"
                                type="search"
                                name="q"
                                value="{{ request('q') }}"
                                placeholder="Search products…"
                                class="store-input min-h-11 rounded-full pl-11 text-sm sm:rounded-xl"
                            />
                            <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                            </svg>
                        </div>
                        <button type="submit" class="btn-brand min-h-11 shrink-0 rounded-full px-4 sm:rounded-xl sm:px-5">Go</button>
                    </form>
                </div>
            </section>

            @if ($featuredCategories->isNotEmpty())
                <section class="border-b border-stone-200/80 bg-stone-50">
                    <div class="mx-auto max-w-7xl px-3 py-5 sm:px-6 sm:py-8 lg:px-8">
                        <h2 class="text-base font-semibold text-stone-900 sm:text-lg">Shop by category</h2>
                        <div class="chip-scroll mt-3 sm:mt-4 sm:grid sm:grid-cols-3 sm:gap-3 lg:grid-cols-6">
                            @foreach ($featuredCategories as $category)
                                <a
                                    href="{{ route('catalog.index', ['category' => $category->slug]) }}"
                                    class="store-card flex min-h-[4.5rem] min-w-[7.5rem] flex-col justify-center px-3 py-3 text-center sm:min-w-0 sm:min-h-20 sm:p-4"
                                >
                                    <span class="text-sm font-semibold text-stone-900">{{ $category->name }}</span>
                                    <span class="mt-0.5 text-[10px] text-stone-500 sm:text-xs">{{ $category->products_count }} items</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif

            @if ($recentlyViewed->isNotEmpty())
                <section class="border-b border-stone-200/80 bg-white">
                    <div class="mx-auto max-w-7xl px-3 py-5 sm:px-6 sm:py-8 lg:px-8">
                        <h2 class="text-base font-semibold text-stone-900 sm:text-lg">Recently viewed</h2>
                        <div class="catalog-grid mt-3 grid grid-cols-2 gap-2 sm:mt-4 sm:gap-4 lg:grid-cols-4 lg:gap-6">
                            @foreach ($recentlyViewed as $product)
                                <x-product-card :product="$product" />
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
        @else
            <section class="border-b border-stone-200/80 bg-white">
                <div class="mx-auto max-w-7xl px-3 py-5 sm:px-6 sm:py-8 lg:px-8">
                    <h1 class="text-xl font-bold tracking-tight text-stone-900 sm:text-3xl">Catalog</h1>
                    <form method="GET" action="{{ route('catalog.index') }}" class="mt-3 flex gap-2 sm:mt-4">
                        <div class="relative min-w-0 flex-1">
                            <label for="search-catalog" class="sr-only">Search products</label>
                            <input id="search-catalog" type="search" name="q" value="{{ request('q') }}" placeholder="Search products…" class="store-input min-h-11 rounded-full pl-11 text-sm sm:rounded-xl" />
                            <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                            </svg>
                        </div>
                        <button type="submit" class="btn-brand min-h-11 shrink-0 rounded-full px-4 sm:rounded-xl">Go</button>
                    </form>
                </div>
            </section>
        @endif

        <div class="mx-auto max-w-7xl px-3 py-4 sm:px-6 sm:py-10 lg:px-8">
            <form method="GET" action="{{ route('catalog.index') }}" class="catalog-filter-panel">
                @if (request('q'))
                    <input type="hidden" name="q" value="{{ request('q') }}">
                @endif

                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between lg:gap-4">
                    @if ($categories->isNotEmpty())
                        <div class="min-w-0 flex-1">
                            <p class="mb-1.5 text-[10px] font-semibold uppercase tracking-wide text-stone-500 sm:mb-2 sm:text-xs">Category</p>
                            <div class="chip-scroll sm:flex-wrap">
                                <a
                                    href="{{ route('catalog.index', array_filter(['q' => request('q'), 'sort' => $sort, 'min_price' => $minPrice, 'max_price' => $maxPrice])) }}"
                                    class="chip min-h-9 px-3 text-xs sm:min-h-10 sm:px-4 sm:text-sm {{ ! request('category') ? 'chip-active' : '' }}"
                                >
                                    All
                                </a>
                                @foreach ($categories as $category)
                                    <a
                                        href="{{ route('catalog.index', array_filter(['category' => $category->slug, 'q' => request('q'), 'sort' => $sort, 'min_price' => $minPrice, 'max_price' => $maxPrice])) }}"
                                        class="chip min-h-9 px-3 text-xs sm:min-h-10 sm:px-4 sm:text-sm {{ request('category') === $category->slug ? 'chip-active' : '' }}"
                                    >
                                        {{ $category->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="flex flex-wrap items-end gap-2 sm:gap-3">
                        <div class="min-w-[7rem] flex-1 sm:flex-none">
                            <label for="sort" class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-stone-500 sm:text-xs">Sort</label>
                            <select id="sort" name="sort" class="store-input min-h-10 w-full text-sm sm:min-h-11 sm:w-44" onchange="this.form.submit()">
                                <option value="newest" @selected($sort === 'newest')>Newest</option>
                                <option value="price_asc" @selected($sort === 'price_asc')>Price: low to high</option>
                                <option value="price_desc" @selected($sort === 'price_desc')>Price: high to low</option>
                            </select>
                        </div>
                        <div class="w-[4.5rem] sm:w-24">
                            <label for="min_price" class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-stone-500 sm:text-xs">Min $</label>
                            <input id="min_price" type="number" name="min_price" min="0" value="{{ $minPrice }}" placeholder="0" class="store-input min-h-10 w-full text-sm sm:min-h-11">
                        </div>
                        <div class="w-[4.5rem] sm:w-24">
                            <label for="max_price" class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-stone-500 sm:text-xs">Max $</label>
                            <input id="max_price" type="number" name="max_price" min="0" value="{{ $maxPrice }}" placeholder="Any" class="store-input min-h-10 w-full text-sm sm:min-h-11">
                        </div>
                        <button type="submit" class="btn-brand-outline min-h-10 shrink-0 px-3 text-sm sm:min-h-11 sm:px-5">Apply</button>
                    </div>
                </div>
            </form>

            @if ($products->isNotEmpty())
                <div class="mb-3 flex items-baseline justify-between gap-2 sm:mb-4">
                    <h2 class="text-sm font-semibold text-stone-900 sm:text-base">
                        {{ $isHome ? 'For you' : 'Results' }}
                    </h2>
                    <p class="text-xs text-stone-500">{{ $products->total() }} items</p>
                </div>
            @endif

            @if ($products->isEmpty())
                <div class="rounded-xl border border-dashed border-stone-300 bg-white px-4 py-12 text-center sm:rounded-2xl sm:px-6 sm:py-16">
                    <p class="text-base font-medium text-stone-900 sm:text-lg">No products found</p>
                    <p class="mt-1 text-sm text-stone-500">Try another search, category, or price range.</p>
                    <a href="{{ route('catalog.index') }}" class="btn-brand-outline mt-5 inline-flex min-h-11 items-center sm:mt-6">Clear filters</a>
                </div>
            @else
                <div class="catalog-grid grid grid-cols-2 gap-2 sm:gap-4 lg:grid-cols-3 lg:gap-6 xl:grid-cols-4">
                    @foreach ($products as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>

                <div class="mt-6 sm:mt-10">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
