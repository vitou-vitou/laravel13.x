<?php

use Livewire\Component;
use App\Models\User;
use App\Models\DatingMatch;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public string $currentTab = 'swipe'; // swipe, browse, matches, profile
    public ?int $activeMatchId = null;
    public ?array $newMatchCelebration = null; // ['user' => ..., 'match_id' => ...]

    protected $listeners = [
        'matchCreated' => 'celebrateMatch',
        'openChat' => 'switchChat',
        'tabChanged' => 'setTab',
    ];

    public function mount(): void
    {
        // Auto-login test user #1 if not authenticated
        if (!Auth::check()) {
            $user = User::first();
            if ($user) {
                Auth::login($user);
            }
        }
    }

    public function switchPersona(int $userId): void
    {
        $user = User::find($userId);
        if ($user) {
            Auth::login($user);
            $this->activeMatchId = null;
            $this->newMatchCelebration = null;
            $this->dispatch('personaSwitched');
        }
    }

    public function setTab(string $tab): void
    {
        $this->currentTab = $tab;
    }

    public function celebrateMatch(array $payload): void
    {
        $this->newMatchCelebration = $payload;
    }

    public function closeCelebration(): void
    {
        $this->newMatchCelebration = null;
    }

    public function goToMatchChat(int $matchId): void
    {
        $this->newMatchCelebration = null;
        $this->activeMatchId = $matchId;
        $this->currentTab = 'matches';
    }

    public function switchChat(int $matchId): void
    {
        $this->activeMatchId = $matchId;
        $this->currentTab = 'matches';
    }
};
?>

<div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col font-sans selection:bg-rose-500 selection:text-white pb-20 md:pb-6">
    {{-- Top Bar: Persona Switcher & App Brand --}}
    <header class="border-b border-slate-800/80 bg-slate-900/70 backdrop-blur-md sticky top-0 z-30 px-4 py-2.5">
        <div class="max-w-4xl mx-auto flex flex-wrap items-center justify-between gap-3">
            {{-- Logo --}}
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-2xl bg-gradient-to-tr from-rose-600 via-pink-500 to-amber-400 flex items-center justify-center shadow-lg shadow-rose-500/25">
                    <svg class="w-5 h-5 text-white fill-current" viewBox="0 0 24 24">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="font-bold tracking-tight text-lg bg-gradient-to-r from-rose-400 via-pink-300 to-amber-300 bg-clip-text text-transparent">Spark</span>
                        <span class="text-[10px] font-semibold tracking-wider uppercase px-1.5 py-0.5 rounded bg-rose-500/10 text-rose-400 border border-rose-500/20">Demo</span>
                    </div>
                </div>
            </div>

            {{-- Dev Persona Quick Switcher Bar --}}
            @if(Auth::check())
                <div class="flex items-center gap-2 text-xs bg-slate-800/80 border border-slate-700/60 rounded-full px-3 py-1.5 shadow-inner">
                    <span class="text-slate-400 font-medium flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Acting as:
                    </span>
                    <select
                        wire:change="switchPersona($event.target.value)"
                        class="bg-transparent text-slate-200 font-semibold focus:outline-none cursor-pointer text-xs pr-1"
                    >
                        @foreach(App\Models\User::with('profile')->get() as $persona)
                            <option value="{{ $persona->id }}" @selected(Auth::id() === $persona->id) class="bg-slate-900 text-slate-100">
                                {{ $persona->name }} ({{ $persona->profile?->gender ?? 'user' }}, {{ $persona->profile?->age ?? '25' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Navigation Desktop Tabs --}}
            <nav class="hidden md:flex items-center gap-1 bg-slate-800/60 p-1 rounded-xl border border-slate-700/50">
                <button
                    wire:click="setTab('swipe')"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-medium transition-all flex items-center gap-1.5 {{ $currentTab === 'swipe' ? 'bg-gradient-to-r from-rose-500 to-pink-500 text-white shadow-md shadow-rose-500/20' : 'text-slate-400 hover:text-slate-200' }}"
                >
                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                    Swipe
                </button>
                <button
                    wire:click="setTab('browse')"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-medium transition-all flex items-center gap-1.5 {{ $currentTab === 'browse' ? 'bg-gradient-to-r from-rose-500 to-pink-500 text-white shadow-md shadow-rose-500/20' : 'text-slate-400 hover:text-slate-200' }}"
                >
                    <svg class="w-3.5 h-3.5 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Explore
                </button>
                <button
                    wire:click="setTab('matches')"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-medium transition-all flex items-center gap-1.5 relative {{ $currentTab === 'matches' ? 'bg-gradient-to-r from-rose-500 to-pink-500 text-white shadow-md shadow-rose-500/20' : 'text-slate-400 hover:text-slate-200' }}"
                >
                    <svg class="w-3.5 h-3.5 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    Matches & Chat
                    @php
                        $matchCount = Auth::check() ? Auth::user()->allMatches()->count() : 0;
                    @endphp
                    @if($matchCount > 0)
                        <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-rose-500 text-white ml-0.5">
                            {{ $matchCount }}
                        </span>
                    @endif
                </button>
                <button
                    wire:click="setTab('profile')"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-medium transition-all flex items-center gap-1.5 {{ $currentTab === 'profile' ? 'bg-gradient-to-r from-rose-500 to-pink-500 text-white shadow-md shadow-rose-500/20' : 'text-slate-400 hover:text-slate-200' }}"
                >
                    <svg class="w-3.5 h-3.5 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    My Profile
                </button>
            </nav>
        </div>
    </header>

    {{-- Main Container --}}
    <main class="flex-1 max-w-4xl w-full mx-auto p-4 flex flex-col items-center justify-center">
        @if($currentTab === 'swipe')
            @livewire('swipe-deck')
        @elseif($currentTab === 'browse')
            @livewire('browse-grid')
        @elseif($currentTab === 'matches')
            @livewire('matches-chat', ['activeMatchId' => $activeMatchId])
        @elseif($currentTab === 'profile')
            @livewire('profile-drawer')
        @endif
    </main>

    {{-- Mobile Bottom Navigation Bar --}}
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-30 bg-slate-900/90 backdrop-blur-lg border-t border-slate-800 px-6 py-2 flex items-center justify-around">
        <button
            wire:click="setTab('swipe')"
            class="flex flex-col items-center gap-1 text-[11px] font-medium transition {{ $currentTab === 'swipe' ? 'text-rose-400' : 'text-slate-500 hover:text-slate-300' }}"
        >
            <div class="p-1 rounded-xl {{ $currentTab === 'swipe' ? 'bg-rose-500/10' : '' }}">
                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
            </div>
            <span>Swipe</span>
        </button>
        <button
            wire:click="setTab('browse')"
            class="flex flex-col items-center gap-1 text-[11px] font-medium transition {{ $currentTab === 'browse' ? 'text-rose-400' : 'text-slate-500 hover:text-slate-300' }}"
        >
            <div class="p-1 rounded-xl {{ $currentTab === 'browse' ? 'bg-rose-500/10' : '' }}">
                <svg class="w-5 h-5 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
            </div>
            <span>Explore</span>
        </button>
        <button
            wire:click="setTab('matches')"
            class="flex flex-col items-center gap-1 text-[11px] font-medium relative transition {{ $currentTab === 'matches' ? 'text-rose-400' : 'text-slate-500 hover:text-slate-300' }}"
        >
            <div class="p-1 rounded-xl relative {{ $currentTab === 'matches' ? 'bg-rose-500/10' : '' }}">
                <svg class="w-5 h-5 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                @if(isset($matchCount) && $matchCount > 0)
                    <span class="absolute top-0 right-0 w-2 h-2 rounded-full bg-rose-500"></span>
                @endif
            </div>
            <span>Matches</span>
        </button>
        <button
            wire:click="setTab('profile')"
            class="flex flex-col items-center gap-1 text-[11px] font-medium transition {{ $currentTab === 'profile' ? 'text-rose-400' : 'text-slate-500 hover:text-slate-300' }}"
        >
            <div class="p-1 rounded-xl {{ $currentTab === 'profile' ? 'bg-rose-500/10' : '' }}">
                <svg class="w-5 h-5 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <span>Profile</span>
        </button>
    </nav>

    {{-- "It's a Match!" Celebration Modal --}}
    @if($newMatchCelebration)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xl animate-fade-in">
            <div class="relative w-full max-w-md bg-gradient-to-b from-slate-900 to-slate-950 border border-rose-500/30 rounded-3xl p-6 shadow-2xl shadow-rose-500/20 text-center overflow-hidden">
                {{-- Background glow --}}
                <div class="absolute -top-24 -left-24 w-48 h-48 bg-rose-500/20 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-24 -right-24 w-48 h-48 bg-pink-500/20 rounded-full blur-3xl"></div>

                <div class="relative z-10">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-500/20 border border-rose-500/40 text-rose-300 text-xs font-semibold uppercase tracking-wider mb-4 animate-bounce">
                        🔥 Mutual Spark!
                    </div>

                    <h2 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-rose-400 via-pink-300 to-amber-300 bg-clip-text text-transparent mb-2">
                        It's a Match!
                    </h2>
                    <p class="text-sm text-slate-300 mb-6">
                        You and <span class="text-rose-400 font-semibold">{{ $newMatchCelebration['user']['name'] }}</span> liked each other!
                    </p>

                    {{-- Intersecting Avatars --}}
                    <div class="flex items-center justify-center -space-x-5 mb-8">
                        <img
                            src="{{ Auth::user()->profile?->avatar_url }}"
                            alt="{{ Auth::user()->name }}"
                            class="w-24 h-24 rounded-full object-cover border-4 border-slate-900 shadow-xl ring-2 ring-rose-500"
                        />
                        <div class="w-10 h-10 rounded-full bg-rose-500 flex items-center justify-center z-20 shadow-lg text-white">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                        </div>
                        <img
                            src="{{ $newMatchCelebration['user']['profile']['avatar_url'] }}"
                            alt="{{ $newMatchCelebration['user']['name'] }}"
                            class="w-24 h-24 rounded-full object-cover border-4 border-slate-900 shadow-xl ring-2 ring-pink-500"
                        />
                    </div>

                    {{-- Actions --}}
                    <div class="flex flex-col gap-2.5">
                        <button
                            wire:click="goToMatchChat({{ $newMatchCelebration['match_id'] }})"
                            class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-rose-500 to-pink-500 hover:from-rose-600 hover:to-pink-600 text-white font-semibold shadow-lg shadow-rose-500/25 transition active:scale-[0.98] flex items-center justify-center gap-2"
                        >
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                            Send a Message
                        </button>
                        <button
                            wire:click="closeCelebration"
                            class="w-full py-3 px-4 rounded-2xl bg-slate-800/80 hover:bg-slate-800 text-slate-300 hover:text-white font-medium text-sm transition"
                        >
                            Keep Swiping
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
