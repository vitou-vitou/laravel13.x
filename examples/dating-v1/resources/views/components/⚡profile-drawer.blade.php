<?php

use Livewire\Component;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public string $name = '';
    public string $occupation = '';
    public string $city = '';
    public int $age = 25;
    public string $gender = 'woman';
    public string $bio = '';
    public string $avatar_url = '';
    public string $interestsString = '';
    public bool $saved = false;

    public function mount(): void
    {
        $this->loadCurrent();
    }

    public function loadCurrent(): void
    {
        $user = Auth::user();
        if (!$user) return;

        $p = $user->profile;
        $this->name = $user->name;
        $this->occupation = $p?->occupation ?? '';
        $this->city = $p?->city ?? 'New York, NY';
        $this->age = $p?->age ?? 25;
        $this->gender = $p?->gender ?? 'woman';
        $this->bio = $p?->bio ?? '';
        $this->avatar_url = $p?->avatar_url ?? '';
        $this->interestsString = is_array($p?->interests) ? implode(', ', $p->interests) : '';
    }

    public function save(): void
    {
        $user = Auth::user();
        if (!$user) return;

        $user->update(['name' => $this->name]);

        $tags = array_values(array_filter(array_map('trim', explode(',', $this->interestsString))));

        Profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'age' => $this->age,
                'gender' => $this->gender,
                'occupation' => $this->occupation,
                'city' => $this->city,
                'bio' => $this->bio,
                'avatar_url' => $this->avatar_url,
                'interests' => $tags,
            ]
        );

        $this->saved = true;
    }
};
?>

<div class="w-full max-w-xl bg-slate-900/80 border border-slate-800 rounded-3xl p-6 shadow-2xl">
    <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-800">
        <div>
            <h2 class="text-xl font-bold text-white tracking-tight">Edit Your Profile</h2>
            <p class="text-xs text-slate-400">Update your photos, bio, and dating preferences</p>
        </div>

        @if($saved)
            <span class="text-xs font-semibold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-3 py-1 rounded-full animate-fade-in">
                ✓ Saved successfully
            </span>
        @endif
    </div>

    <form wire:submit="save" class="space-y-4">
        {{-- Avatar Preview & URL --}}
        <div class="flex items-center gap-4 p-4 rounded-2xl bg-slate-950/60 border border-slate-800/80">
            <img
                src="{{ $avatar_url ?: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=800' }}"
                alt="Profile Preview"
                class="w-16 h-16 rounded-full object-cover ring-2 ring-rose-500 shrink-0"
            />
            <div class="flex-1 min-w-0">
                <label class="block text-xs font-medium text-slate-300 mb-1">Avatar Photo URL</label>
                <input
                    type="text"
                    wire:model="avatar_url"
                    placeholder="https://images.unsplash.com/..."
                    class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500"
                />
            </div>
        </div>

        {{-- Name & Age --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Display Name</label>
                <input
                    type="text"
                    wire:model="name"
                    required
                    class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-rose-500"
                />
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Age</label>
                <input
                    type="number"
                    wire:model="age"
                    min="18"
                    max="99"
                    required
                    class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-rose-500"
                />
            </div>
        </div>

        {{-- Gender & City --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Gender</label>
                <select
                    wire:model="gender"
                    class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-rose-500 cursor-pointer"
                >
                    <option value="woman">Woman</option>
                    <option value="man">Man</option>
                    <option value="non-binary">Non-binary</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">City / Neighborhood</label>
                <input
                    type="text"
                    wire:model="city"
                    placeholder="e.g. Brooklyn, NY"
                    class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-rose-500"
                />
            </div>
        </div>

        {{-- Occupation --}}
        <div>
            <label class="block text-xs font-medium text-slate-300 mb-1">Occupation</label>
            <input
                type="text"
                wire:model="occupation"
                placeholder="e.g. Product Designer @ Figma"
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-rose-500"
            />
        </div>

        {{-- Bio --}}
        <div>
            <label class="block text-xs font-medium text-slate-300 mb-1">About Me (Bio)</label>
            <textarea
                wire:model="bio"
                rows="3"
                placeholder="Write what makes you unique, your simple pleasures, ideal Sunday..."
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 leading-relaxed"
            ></textarea>
        </div>

        {{-- Interests tags --}}
        <div>
            <label class="block text-xs font-medium text-slate-300 mb-1">Interests & Hobbies (comma-separated)</label>
            <input
                type="text"
                wire:model="interestsString"
                placeholder="Coffee, Vinyl, Hiking, Cinema, Tacos"
                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500"
            />
        </div>

        <div class="pt-2">
            <button
                type="submit"
                class="w-full py-3 rounded-xl bg-gradient-to-r from-rose-500 to-pink-500 hover:from-rose-600 hover:to-pink-600 text-white font-semibold text-xs shadow-lg shadow-rose-500/20 transition active:scale-[0.99]"
            >
                Save Profile Changes
            </button>
        </div>
    </form>
</div>
