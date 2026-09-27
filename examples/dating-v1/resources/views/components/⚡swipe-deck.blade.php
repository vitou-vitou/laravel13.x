<?php

use Livewire\Component;
use App\Models\User;
use App\Models\Swipe;
use App\Models\DatingMatch;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public array $queue = [];
    public ?array $currentCandidate = null;

    protected $listeners = [
        'personaSwitched' => 'loadQueue',
        'refreshDeck' => 'loadQueue',
    ];

    public function mount(): void
    {
        $this->loadQueue();
    }

    public function loadQueue(): void
    {
        if (!Auth::check()) {
            return;
        }

        $authId = Auth::id();
        $swipedIds = Swipe::where('swiper_id', $authId)->pluck('swiped_id')->toArray();
        $swipedIds[] = $authId; // exclude self

        $candidates = User::with('profile')
            ->whereNotIn('id', $swipedIds)
            ->inRandomOrder()
            ->take(15)
            ->get()
            ->toArray();

        $this->queue = $candidates;
        $this->currentCandidate = count($this->queue) > 0 ? $this->queue[0] : null;
    }

    public function swipe(string $type): void
    {
        if (!$this->currentCandidate || !Auth::check()) {
            return;
        }

        $authId = Auth::id();
        $candidateId = $this->currentCandidate['id'];

        // Save swipe
        Swipe::updateOrCreate(
            ['swiper_id' => $authId, 'swiped_id' => $candidateId],
            ['type' => $type]
        );

        // Check mutual like / superlike
        if (in_array($type, ['like', 'superlike'])) {
            $otherSwipe = Swipe::where('swiper_id', $candidateId)
                ->where('swiped_id', $authId)
                ->whereIn('type', ['like', 'superlike'])
                ->first();

            if ($otherSwipe) {
                // Mutual match found!
                $userOneId = min($authId, $candidateId);
                $userTwoId = max($authId, $candidateId);

                $match = DatingMatch::firstOrCreate(
                    ['user_one_id' => $userOneId, 'user_two_id' => $userTwoId],
                    ['matched_at' => now()]
                );

                $this->dispatch('matchCreated', [
                    'user' => $this->currentCandidate,
                    'match_id' => $match->id,
                ]);
            }
        }

        // Advance candidate
        array_shift($this->queue);
        $this->currentCandidate = count($this->queue) > 0 ? $this->queue[0] : null;
    }

    public function resetDeck(): void
    {
        if (Auth::check()) {
            Swipe::where('swiper_id', Auth::id())->delete();
            $this->loadQueue();
        }
    }
};
?>

<div class="w-full max-w-sm mx-auto flex flex-col items-center">
    @if($currentCandidate)
        {{-- Card Container with Alpine drag / touch simulation --}}
        <div
            x-data="{
                startX: 0,
                currentX: 0,
                dragging: false,
                threshold: 80,
                get rotate() { return (this.currentX / 15).toFixed(1); },
                get status() {
                    if (this.currentX > this.threshold) return 'LIKE';
                    if (this.currentX < -this.threshold) return 'NOPE';
                    return '';
                },
                start(e) {
                    this.dragging = true;
                    this.startX = e.type.includes('mouse') ? e.clientX : e.touches[0].clientX;
                },
                move(e) {
                    if (!this.dragging) return;
                    let x = e.type.includes('mouse') ? e.clientX : e.touches[0].clientX;
                    this.currentX = x - this.startX;
                },
                end() {
                    if (!this.dragging) return;
                    this.dragging = false;
                    if (this.currentX > this.threshold) {
                        $wire.swipe('like');
                    } else if (this.currentX < -this.threshold) {
                        $wire.swipe('pass');
                    }
                    this.currentX = 0;
                }
            }"
            @mousedown="start($event)"
            @mousemove.window="move($event)"
            @mouseup.window="end()"
            @touchstart="start($event)"
            @touchmove.window="move($event)"
            @touchend.window="end()"
            class="relative w-full h-[540px] select-none cursor-grab active:cursor-grabbing group transition-transform"
            :style="`transform: translateX(${currentX}px) rotate(${rotate}deg); transition: ${dragging ? 'none' : 'transform 0.3s ease'};`"
        >
            {{-- Behind Card Preview (Stack effect) --}}
            @if(isset($queue[1]))
                <div class="absolute inset-0 rounded-3xl bg-slate-800/80 border border-slate-700/50 transform scale-95 translate-y-3 -z-10 shadow-xl overflow-hidden opacity-60">
                    <img src="{{ $queue[1]['profile']['avatar_url'] ?? '' }}" alt="" class="w-full h-full object-cover blur-[1px]">
                </div>
            @endif

            {{-- Main Top Card --}}
            <div class="relative w-full h-full rounded-3xl overflow-hidden shadow-2xl shadow-slate-950/80 border border-slate-800 bg-slate-900 flex flex-col justify-end">
                {{-- Background Image --}}
                <img
                    src="{{ $currentCandidate['profile']['avatar_url'] ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=800' }}"
                    alt="{{ $currentCandidate['name'] }}"
                    class="absolute inset-0 w-full h-full object-cover pointer-events-none"
                />

                {{-- Gradient Scrim --}}
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/50 to-transparent pointer-events-none"></div>

                {{-- Swipe Stamp Indicators (Dynamic via Alpine) --}}
                <div
                    x-show="status === 'LIKE'"
                    x-cloak
                    class="absolute top-8 left-6 border-4 border-emerald-500 text-emerald-400 font-extrabold text-2xl tracking-widest px-3 py-1 rounded-xl uppercase rotate-[-15deg] shadow-lg bg-slate-950/40 backdrop-blur-sm"
                >
                    LIKE
                </div>
                <div
                    x-show="status === 'NOPE'"
                    x-cloak
                    class="absolute top-8 right-6 border-4 border-rose-500 text-rose-400 font-extrabold text-2xl tracking-widest px-3 py-1 rounded-xl uppercase rotate-[15deg] shadow-lg bg-slate-950/40 backdrop-blur-sm"
                >
                    NOPE
                </div>

                {{-- Profile Info Overlay --}}
                <div class="relative z-10 p-5 text-left pointer-events-none">
                    <div class="flex items-baseline gap-2 mb-1">
                        <h2 class="text-2xl font-bold tracking-tight text-white drop-shadow-md">
                            {{ $currentCandidate['name'] }}
                        </h2>
                        <span class="text-xl font-medium text-slate-200">
                            {{ $currentCandidate['profile']['age'] ?? 25 }}
                        </span>
                    </div>

                    @if(!empty($currentCandidate['profile']['occupation']))
                        <div class="flex items-center gap-1.5 text-xs text-slate-300 mb-2 drop-shadow">
                            <svg class="w-3.5 h-3.5 text-rose-400 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>{{ $currentCandidate['profile']['occupation'] }}</span>
                        </div>
                    @endif

                    <div class="flex items-center gap-1 text-xs text-slate-400 mb-3">
                        <svg class="w-3.5 h-3.5 text-slate-400 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>{{ $currentCandidate['profile']['city'] ?? 'New York, NY' }} • {{ $currentCandidate['profile']['distance_km'] ?? 5 }} km away</span>
                    </div>

                    @if(!empty($currentCandidate['profile']['bio']))
                        <p class="text-xs text-slate-200/90 line-clamp-2 leading-relaxed mb-3">
                            {{ $currentCandidate['profile']['bio'] }}
                        </p>
                    @endif

                    {{-- Interests pills --}}
                    @if(!empty($currentCandidate['profile']['interests']))
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($currentCandidate['profile']['interests'] as $tag)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-slate-900/80 text-rose-300 border border-rose-500/20 backdrop-blur-sm">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Floating Action Controls (Pass ❌, Superlike ⭐, Like 💚) --}}
        <div class="flex items-center justify-center gap-5 mt-5">
            {{-- Pass Button --}}
            <button
                wire:click="swipe('pass')"
                class="w-14 h-14 rounded-full bg-slate-900 border border-slate-700/80 hover:border-rose-500/50 flex items-center justify-center text-rose-500 shadow-xl shadow-slate-950/60 hover:scale-110 active:scale-95 transition-all group"
                title="Pass (Swipe Left)"
            >
                <svg class="w-6 h-6 stroke-current fill-none" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            {{-- Superlike Button --}}
            <button
                wire:click="swipe('superlike')"
                class="w-12 h-12 rounded-full bg-slate-900 border border-slate-700/80 hover:border-blue-500/50 flex items-center justify-center text-blue-400 shadow-xl shadow-slate-950/60 hover:scale-110 active:scale-95 transition-all group"
                title="Superlike"
            >
                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
            </button>

            {{-- Like Button --}}
            <button
                wire:click="swipe('like')"
                class="w-14 h-14 rounded-full bg-gradient-to-tr from-emerald-600 to-teal-400 border border-emerald-400/40 flex items-center justify-center text-white shadow-xl shadow-emerald-600/30 hover:scale-110 active:scale-95 transition-all group"
                title="Like (Swipe Right)"
            >
                <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
            </button>
        </div>

        <p class="text-[11px] text-slate-500 mt-3 text-center">
            Drag card left/right or click buttons • <button wire:click="resetDeck" class="text-rose-400 hover:underline">Reset swipes</button>
        </p>

    @else
        {{-- Empty State: Deck Finished --}}
        <div class="w-full h-[480px] rounded-3xl bg-slate-900/60 border border-slate-800 flex flex-col items-center justify-center p-8 text-center">
            <div class="w-16 h-16 rounded-full bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400 mb-4">
                <svg class="w-8 h-8 fill-none stroke-current" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-white mb-1">You've seen everyone nearby!</h3>
            <p class="text-xs text-slate-400 max-w-xs mb-6 leading-relaxed">
                Check back later for new people, switch your demo persona up top, or reset your swipe history to review candidates again.
            </p>
            <div class="flex gap-2">
                <button
                    wire:click="resetDeck"
                    class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-rose-500 to-pink-500 hover:from-rose-600 hover:to-pink-600 text-white text-xs font-semibold shadow-md shadow-rose-500/20 transition"
                >
                    Reset Deck & Reswipe
                </button>
            </div>
        </div>
    @endif
</div>
