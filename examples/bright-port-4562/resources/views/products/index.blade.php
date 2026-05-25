<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Product Inventory
            <span class="ml-2 text-sm font-normal text-gray-500">({{ $products->total() }} products)</span>
        </h2>
    </x-slot>

    <div class="py-6" x-data="{ filtersOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Stats --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Products</div>
                    <div class="mt-1 text-2xl font-bold text-gray-800">{{ $stats['total'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">Low Stock</div>
                    <div class="mt-1 text-2xl font-bold text-yellow-600">{{ $stats['low_stock'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">Out of Stock</div>
                    <div class="mt-1 text-2xl font-bold text-red-600">{{ $stats['out'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider">Inventory Value</div>
                    <div class="mt-1 text-2xl font-bold text-green-700">${{ number_format($stats['value'], 0) }}</div>
                </div>
            </div>

            {{-- Mobile filter toggle --}}
            <div class="lg:hidden mb-3">
                <button @click="filtersOpen = !filtersOpen"
                    class="flex items-center gap-2 text-sm font-medium text-indigo-600 border border-indigo-300 rounded-md px-3 py-2 hover:bg-indigo-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
                    </svg>
                    <span x-text="filtersOpen ? 'Hide Filters' : 'Show Filters'">Show Filters</span>
                </button>
            </div>

            <div class="flex flex-col lg:flex-row gap-6">

                {{-- FILTER SIDEBAR --}}
                <aside class="w-full lg:w-56 lg:flex-shrink-0 lg:!block" x-show="filtersOpen" x-transition>
                    <div class="bg-white rounded-lg shadow p-5">
                        <h3 class="font-semibold text-gray-700 mb-4">Filters</h3>
                        <form method="GET" action="/">

                            <div class="mb-4">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                                <input type="text" name="search" value="{{ request('search') }}"
                                    placeholder="name, SKU, supplier..."
                                    class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <div class="mb-4">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Category</label>
                                <select name="category" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">All</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Stock Status</label>
                                <select name="stock" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">All</option>
                                    <option value="in_stock"     @selected(request('stock') === 'in_stock')>In Stock</option>
                                    <option value="low_stock"    @selected(request('stock') === 'low_stock')>Low Stock</option>
                                    <option value="out_of_stock" @selected(request('stock') === 'out_of_stock')>Out of Stock</option>
                                </select>
                            </div>

                            <div class="flex gap-2 mt-5">
                                <button type="submit"
                                    class="flex-1 bg-indigo-600 text-white text-sm font-medium py-2 px-3 rounded-md hover:bg-indigo-700">
                                    Apply
                                </button>
                                <a href="/"
                                    class="flex-1 text-center bg-gray-100 text-gray-700 text-sm font-medium py-2 px-3 rounded-md hover:bg-gray-200">
                                    Reset
                                </a>
                            </div>

                        </form>
                    </div>
                </aside>

                {{-- TABLE --}}
                <div class="flex-1 min-w-0">
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">SKU</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Product</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Category</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Qty</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Price</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Supplier</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($products as $product)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-gray-400 font-mono text-xs whitespace-nowrap">{{ $product->sku }}</td>
                                            <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">{{ $product->name }}</td>
                                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $product->category }}</td>
                                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                                <span class="font-semibold {{ $product->quantity === 0 ? 'text-red-600' : ($product->quantity <= $product->low_stock_threshold ? 'text-yellow-600' : 'text-gray-800') }}">
                                                    {{ $product->quantity }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-right text-gray-700 whitespace-nowrap">${{ number_format($product->price, 2) }}</td>
                                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $product->supplier ?? '—' }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                @php $s = $product->stock_status; @endphp
                                                @if ($s === 'in_stock')
                                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">In Stock</span>
                                                @elseif ($s === 'low_stock')
                                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Low Stock</span>
                                                @else
                                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Out of Stock</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-4 py-10 text-center text-gray-400">No products found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($products->hasPages())
                            <div class="px-4 py-3 border-t border-gray-200">
                                {{ $products->links() }}
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
