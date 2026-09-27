<?php

use Livewire\Component;
use App\Models\User;
use App\Models\Swipe;
use App\Models\DatingMatch;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public string $filterGender = 'all';
    public int $minAge = 20;
    public int $maxAge = 35;
    public string $search = '';

    public function quickLike(int $userId): void
    {
        if (!Auth::check() || Auth::id() === $userId) return;

        $authId = Auth::id();
        Swipe::updateOrCreate(
            ['swiper_id' => $authId, 'swiped_id' => $userId],
            ['type' => 'like']
        );

        // Check mutual like
        $otherSwipe = Swipe::where('swiper_id', $userId)
            ->where('swiped_id', $authId)
            ->whereIn('type', ['like', 'superlike'])
            ->first();

        if ($otherSwipe) {
            $userOneId = min($authId, $userId);
            $userTwoId = max($authId, $userId);

            $match = DatingMatch::firstOrCreate(
                ['user_one_id' => $userOneId, 'user_two_id' => $userTwoId],
                ['matched_at' => now()]
            );

            $candidate = User::with('profile')->find($userId);
            $this->dispatch('matchCreated', [
                'user' => $candidate->toArray(),
                'match_id' => $match->id,
            ]);
        }
    }
};
?>

<div class="w-full">
    {{-- Header & Filters --}}
    <div class="mb-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 bg-slate-900/60 p-4 rounded-2xl border border-slate-800">
        <div>
            <h2 class="text-xl font-bold tracking-tight text-white flex items-center gap-2">
                <span>Explore Nearby</span>
                <span class="text-xs font-normal text-slate-400">Discover profiles around New York</span>
            </h2>
        </div>

        {{-- Filters toolbar --}}
        <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
            <div class="flex items-center gap-1 bg-slate-800 rounded-xl p-1 border border-slate-700/60 text-xs">
                <button
                    wire:click="$set('filterGender', 'all')"
                    class="px-2.5 py-1 rounded-lg transition {{ $filterGender === 'all' ? 'bg-rose-500 text-white font-semibold' : 'text-slate-400 hover:text-slate-200' }}"
                >
                    All
                </button>
                <button
                    wire:click="$set('filterGender', 'woman')"
                    class="px-2.5 py-1 rounded-lg transition {{ $filterGender === 'woman' ? 'bg-rose-500 text-white font-semibold' : 'text-slate-400 hover:text-slate-200' }}"
                >
                    Women
                </button>
                <button
                    wire:click="$set('filterGender', 'man')"
                    class="px-2.5 py-1 rounded-lg transition {{ $filterGender === 'man' ? 'bg-rose-500 text-white font-semibold' : 'text-slate-400 hover:text-slate-200' }}"
                >
                    Men
                </button>
            </div>

            <div class="flex items-center gap-2 bg-slate-800 border border-slate-700/60 px-3 py-1.5 rounded-xl text-xs text-slate-300">
                <span class="text-slate-400">Age:</span>
                <select wire:model.live="minAge" class="bg-transparent focus:outline-none cursor-pointer">
                    <option value="20" class="bg-slate-900">20</option>
                    <option value="24" class="bg-slate-900">24</option>
                    <option value="28" class="bg-slate-900">28</option>
                </select>
                <span>-</span>
                <select wire:model.live="maxAge" class="bg-transparent focus:outline-none cursor-pointer">
                    <option value="30" class="bg-slate-900">30</option>
                    <option value="35" class="bg-slate-900">35</option>
                    <option value="40" class="bg-slate-900">40</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Grid --}}
    @php
        $query = User::with('profile')->where('id', '!=', Auth::id());
        if ($filterGender !== 'all') {
            $query->whereHas('profile', fn($q) => $q->where('gender', $filterGender));
        }
        $query->whereHas('profile', fn($q) => $q->whereBetween('age', [$minAge, $maxAge]));
        $users = $query->get();

        $myLikedIds = Auth::check()
            ? Swipe::where('swiper_id', Auth::id())->whereIn('type', ['like', 'superlike'])->pluck('swiped_id')->toArray()
            : [];
    @endphp

    @if($users->isEmpty())
        <div class="py-16 text-center text-slate-400 bg-slate-900/40 rounded-3xl border border-slate-800">
            <p class="text-sm">No profiles found matching current filters.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($users as $user)
                @php
                    $isLiked = in_array($user->id, $myLikedIds);
                @endphp
                <div class="group relative rounded-2xl overflow-hidden bg-slate-900 border border-slate-800 hover:border-slate-700 shadow-lg transition flex flex-col justify-end h-80">
                    <img
                        src="{{ $user->profile?->avatar_url }}"
                        alt="{{ $user->name }}"
                        class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-500"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent"></div>

                    {{-- Top distance badge --}}
                    <div class="absolute top-3 left-3 bg-slate-950/60 backdrop-blur-md px-2.5 py-1 rounded-full text-[10px] font-medium text-slate-300 border border-slate-700/50 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        {{ $user->profile?->distance_km ?? 3 }} km away
                    </div>

                    {{-- Bottom info --}}
                    <div class="relative z-10 p-4">
                        <div class="flex items-baseline justify-between mb-1">
                            <h3 class="font-bold text-white text-base truncate">
                                {{ $user->name }}, <span class="text-sm font-medium text-slate-300">{{ $user->profile?->age ?? 25 }}</span>
                            </h3>
                        </div>

                        <p class="text-xs text-slate-300 truncate mb-1">
                            {{ $user->profile?->occupation ?? 'Creative' }}
                        </p>

                        <p class="text-[11px] text-slate-400 line-clamp-2 leading-relaxed mb-3">
                            {{ $user->profile?->bio }}
                        </p>

                        <div class="flex items-center justify-between pt-2 border-t border-slate-800/80">
                            <span class="text-[11px] text-slate-400">{{ $user->profile?->city }}</span>

                            <button
                                wire:click="quickLike({{ $user->id }})"
                                class="px-3 py-1 rounded-xl text-xs font-semibold transition flex items-center gap-1.5 {{ $isLiked ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'bg-gradient-to-r from-rose-500 to-pink-500 text-white hover:opacity-90 shadow-md shadow-rose-500/20' }}"
                            >
                                @if($isLiked)
                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                                    Liked
                                @else
                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                                    Like
                                @endif
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
