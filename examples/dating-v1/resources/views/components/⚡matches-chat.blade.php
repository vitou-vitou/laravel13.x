<?php

use Livewire\Component;
use App\Models\User;
use App\Models\DatingMatch;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public ?int $activeMatchId = null;
    public string $messageText = '';

    protected $listeners = [
        'personaSwitched' => 'resetActive',
    ];

    public function mount(?int $activeMatchId = null): void
    {
        $this->activeMatchId = $activeMatchId;
    }

    public function resetActive(): void
    {
        $this->activeMatchId = null;
    }

    public function selectMatch(int $matchId): void
    {
        $this->activeMatchId = $matchId;
    }

    public function sendMessage(): void
    {
        $text = trim($this->messageText);
        if (empty($text) || !$this->activeMatchId || !Auth::check()) {
            return;
        }

        Message::create([
            'match_id' => $this->activeMatchId,
            'sender_id' => Auth::id(),
            'body' => $text,
        ]);

        $this->messageText = '';
    }
};
?>

<div class="w-full bg-slate-900/70 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl flex flex-col md:flex-row h-[620px]">
    @php
        $myUser = Auth::user();
        $matches = $myUser ? $myUser->allMatches()->get() : collect();
        $activeMatch = $activeMatchId ? DatingMatch::with(['userOne.profile', 'userTwo.profile', 'messages.sender'])->find($activeMatchId) : null;
        $otherActiveUser = ($activeMatch && $myUser) ? $myUser->getOtherUser($activeMatch) : null;
    @endphp

    {{-- Left Column: Matches List --}}
    <div class="w-full md:w-80 border-b md:border-b-0 md:border-r border-slate-800 flex flex-col bg-slate-900/90 shrink-0 {{ $activeMatchId ? 'hidden md:flex' : 'flex' }}">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between">
            <h2 class="font-bold text-white text-base flex items-center gap-2">
                <span>Matches</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-500/20 text-rose-300">
                    {{ $matches->count() }}
                </span>
            </h2>
        </div>

        {{-- Horizontal avatar bubbles for instant matches --}}
        @if($matches->isNotEmpty())
            <div class="p-3 border-b border-slate-800 overflow-x-auto flex items-center gap-3 no-scrollbar">
                @foreach($matches as $m)
                    @php $other = $myUser->getOtherUser($m); @endphp
                    <button
                        wire:click="selectMatch({{ $m->id }})"
                        class="flex flex-col items-center gap-1 group shrink-0 focus:outline-none"
                    >
                        <div class="relative">
                            <img
                                src="{{ $other->profile?->avatar_url }}"
                                alt="{{ $other->name }}"
                                class="w-13 h-13 rounded-full object-cover ring-2 {{ $activeMatchId === $m->id ? 'ring-rose-500 scale-105' : 'ring-slate-700 group-hover:ring-rose-400' }} transition"
                            />
                            <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full bg-emerald-400 border-2 border-slate-900"></span>
                        </div>
                        <span class="text-[10px] text-slate-300 truncate w-14 text-center font-medium">
                            {{ explode(' ', $other->name)[0] }}
                        </span>
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Conversation List --}}
        <div class="flex-1 overflow-y-auto divide-y divide-slate-800/60">
            @forelse($matches as $match)
                @php
                    $other = $myUser->getOtherUser($match);
                    $lastMsg = $match->latestMessage;
                    $isActive = $activeMatchId === $match->id;
                @endphp
                <button
                    wire:click="selectMatch({{ $match->id }})"
                    class="w-full text-left p-3.5 flex items-center gap-3 transition hover:bg-slate-800/60 {{ $isActive ? 'bg-slate-800/80 border-l-4 border-rose-500' : '' }}"
                >
                    <img
                        src="{{ $other->profile?->avatar_url }}"
                        alt="{{ $other->name }}"
                        class="w-12 h-12 rounded-full object-cover shrink-0"
                    />
                    <div class="flex-1 min-w-0">
                        <div class="flex items-baseline justify-between mb-0.5">
                            <h4 class="text-sm font-semibold text-white truncate">
                                {{ $other->name }}
                            </h4>
                            @if($lastMsg)
                                <span class="text-[10px] text-slate-500">
                                    {{ $lastMsg->created_at->shortAbsoluteDiffForHumans() }}
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-400 truncate">
                            @if($lastMsg)
                                <span class="{{ $lastMsg->sender_id === Auth::id() ? 'text-slate-500' : 'text-slate-300 font-medium' }}">
                                    {{ $lastMsg->sender_id === Auth::id() ? 'You: ' : '' }}{{ $lastMsg->body }}
                                </span>
                            @else
                                <span class="text-rose-400 italic text-[11px]">New match! Say hello 👋</span>
                            @endif
                        </p>
                    </div>
                </button>
            @empty
                <div class="p-8 text-center text-slate-500 text-xs">
                    <p>No matches yet.</p>
                    <p class="mt-1">Go to <button wire:click="$dispatch('tabChanged', { tab: 'swipe' })" class="text-rose-400 hover:underline">Swipe</button> to find your sparks!</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Right Column: Chat Box (Polls every 2 seconds) --}}
    <div class="flex-1 flex flex-col bg-slate-950/60 relative {{ !$activeMatchId ? 'hidden md:flex items-center justify-center' : 'flex' }}" wire:poll.2s>
        @if($activeMatch && $otherActiveUser)
            {{-- Chat Header --}}
            <div class="px-4 py-3 border-b border-slate-800 bg-slate-900/80 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    {{-- Mobile back button --}}
                    <button wire:click="$set('activeMatchId', null)" class="md:hidden text-slate-400 hover:text-white mr-1">
                        <svg class="w-5 h-5 fill-none stroke-current" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <img
                        src="{{ $otherActiveUser->profile?->avatar_url }}"
                        alt="{{ $otherActiveUser->name }}"
                        class="w-10 h-10 rounded-full object-cover ring-1 ring-slate-700"
                    />
                    <div>
                        <h3 class="text-sm font-bold text-white flex items-center gap-1.5">
                            {{ $otherActiveUser->name }}
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        </h3>
                        <p class="text-[11px] text-slate-400">
                            {{ $otherActiveUser->profile?->occupation ?? 'Designer' }} • {{ $otherActiveUser->profile?->city ?? 'New York' }}
                        </p>
                    </div>
                </div>

                <button
                    wire:click="$dispatch('tabChanged', { tab: 'browse' })"
                    class="text-xs text-slate-400 hover:text-rose-400 flex items-center gap-1"
                >
                    Profile info
                </button>
            </div>

            {{-- Message History Scroll Area --}}
            <div
                class="flex-1 overflow-y-auto p-4 space-y-3 flex flex-col"
                x-data
                x-init="$el.scrollTop = $el.scrollHeight"
                @scroll-bottom.window="$el.scrollTop = $el.scrollHeight"
            >
                <div class="text-center my-2">
                    <span class="text-[10px] text-slate-500 bg-slate-900/60 border border-slate-800 px-3 py-1 rounded-full">
                        Matched {{ $activeMatch->matched_at->format('M d, Y') }}
                    </span>
                </div>

                @forelse($activeMatch->messages as $msg)
                    @php $isMe = $msg->sender_id === Auth::id(); @endphp
                    <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }}">
                        <div class="max-w-[78%] rounded-2xl px-4 py-2.5 text-xs leading-relaxed shadow-md {{ $isMe ? 'bg-gradient-to-r from-rose-500 to-pink-500 text-white rounded-br-none' : 'bg-slate-800 text-slate-200 border border-slate-700/60 rounded-bl-none' }}">
                            {{ $msg->body }}
                        </div>
                        <span class="text-[9px] text-slate-500 mt-1 px-1">
                            {{ $msg->created_at->format('g:i A') }}
                        </span>
                    </div>
                @empty
                    <div class="flex-1 flex flex-col items-center justify-center text-center p-6 text-slate-500">
                        <div class="w-12 h-12 rounded-full bg-rose-500/10 flex items-center justify-center text-rose-400 mb-2">
                            <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>
                        </div>
                        <p class="text-xs font-semibold text-slate-300">Break the ice!</p>
                        <p class="text-[11px] text-slate-500 max-w-xs mt-1">
                            Send a quick greeting to kick off your conversation with {{ $otherActiveUser->name }}.
                        </p>
                    </div>
                @endforelse
            </div>

            {{-- Message Input Bar --}}
            <form wire:submit="sendMessage" class="p-3 border-t border-slate-800 bg-slate-900/90 flex items-center gap-2">
                <input
                    type="text"
                    wire:model="messageText"
                    placeholder="Type a thoughtful message..."
                    class="flex-1 bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 transition"
                />
                <button
                    type="submit"
                    class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-rose-500 to-pink-500 hover:from-rose-600 hover:to-pink-600 text-white font-medium text-xs shadow-md shadow-rose-500/20 transition active:scale-95 flex items-center gap-1.5"
                >
                    <span>Send</span>
                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                </button>
            </form>

        @else
            {{-- Empty chat placeholder --}}
            <div class="flex flex-col items-center justify-center p-8 text-center text-slate-500">
                <div class="w-16 h-16 rounded-full bg-slate-800/80 border border-slate-700/50 flex items-center justify-center text-slate-400 mb-3">
                    <svg class="w-8 h-8 fill-none stroke-current" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <h3 class="text-sm font-bold text-white mb-1">Select a Match to Chat</h3>
                <p class="text-xs text-slate-400 max-w-xs">
                    Choose one of your active matches from the list on the left to view and send messages.
                </p>
            </div>
        @endif
    </div>
</div>
