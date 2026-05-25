<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            User Directory
            <span class="ml-2 text-sm font-normal text-gray-500">({{ $users->total() }} results)</span>
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex gap-6">

                {{-- FILTER SIDEBAR --}}
                <aside class="w-64 flex-shrink-0">
                    <div class="bg-white rounded-lg shadow p-5">
                        <h3 class="font-semibold text-gray-700 mb-4">Filters</h3>
                        <form method="GET" action="/" x-data="{ period: '{{ request('period') }}' }">

                            {{-- Keyword --}}
                            <div class="mb-4">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Keyword</label>
                                <input type="text" name="keyword" value="{{ request('keyword') }}"
                                    placeholder="name, email, city..."
                                    class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            {{-- Country --}}
                            <div class="mb-4">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Country</label>
                                <select name="country" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">Any</option>
                                    @foreach ($countries as $c)
                                        <option value="{{ $c }}" @selected(request('country') === $c)>{{ $c }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- City --}}
                            <div class="mb-4">
                                <label class="block text-xs font-medium text-gray-600 mb-1">City</label>
                                <select name="city" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">Any</option>
                                    @foreach ($cities as $c)
                                        <option value="{{ $c }}" @selected(request('city') === $c)>{{ $c }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Device --}}
                            <div class="mb-4">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Device</label>
                                <select name="device_type" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">Any</option>
                                    @foreach (['web', 'mobile', 'tablet'] as $d)
                                        <option value="{{ $d }}" @selected(request('device_type') === $d)>{{ ucfirst($d) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Signup Source --}}
                            <div class="mb-4">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Signup Source</label>
                                <select name="signup_source" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">Any</option>
                                    @foreach (['organic', 'referral', 'social', 'paid'] as $s)
                                        <option value="{{ $s }}" @selected(request('signup_source') === $s)>{{ ucfirst($s) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Has Avatar --}}
                            <div class="mb-4">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Avatar</label>
                                <select name="has_avatar" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">Any</option>
                                    <option value="1" @selected(request('has_avatar') === '1')>Has avatar</option>
                                    <option value="0" @selected(request('has_avatar') === '0')>No avatar</option>
                                </select>
                            </div>

                            {{-- Status --}}
                            <div class="mb-4">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                                <select name="is_active" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">Any</option>
                                    <option value="1" @selected(request('is_active') === '1')>Active</option>
                                    <option value="0" @selected(request('is_active') === '0')>Inactive</option>
                                </select>
                            </div>

                            {{-- Period --}}
                            <div class="mb-4">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Joined Period</label>
                                <select name="period" x-model="period" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    <option value="">Any time</option>
                                    <option value="day">Today</option>
                                    <option value="week">This week</option>
                                    <option value="month">This month</option>
                                    <option value="year">This year</option>
                                    <option value="custom">Custom range</option>
                                </select>
                            </div>

                            {{-- Custom date range (Alpine x-show) --}}
                            <div x-show="period === 'custom'" x-cloak class="mb-4 space-y-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">From</label>
                                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                                        class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">To</label>
                                    <input type="date" name="end_date" value="{{ request('end_date') }}"
                                        class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                </div>
                            </div>

                            {{-- Buttons --}}
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

                {{-- RESULTS TABLE --}}
                <div class="flex-1 min-w-0">
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Device</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($users as $user)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-gray-400">{{ $user->id }}</td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-3">
                                                    @if ($user->avatar)
                                                        <img src="{{ $user->avatar }}" alt="" class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                                                    @else
                                                        <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
                                                            <span class="text-xs text-gray-500">{{ strtoupper(substr($user->username, 0, 1)) }}</span>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="font-medium text-gray-900">{{ $user->username }}</div>
                                                        <div class="text-gray-400 text-xs">{{ $user->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-gray-600">
                                                {{ $user->city ?? '—' }}, {{ $user->country ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-600">{{ $user->device_type ?? '—' }}</td>
                                            <td class="px-4 py-3 text-gray-600">{{ $user->signup_source ?? '—' }}</td>
                                            <td class="px-4 py-3">
                                                @php
                                                    $isActive = $user->last_login_at && $user->last_login_at->gte(now()->subDays(30));
                                                @endphp
                                                @if ($isActive)
                                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                                                @else
                                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $user->created_at->format('M d, Y') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-4 py-10 text-center text-gray-400">No users found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($users->hasPages())
                            <div class="px-4 py-3 border-t border-gray-200">
                                {{ $users->links() }}
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
