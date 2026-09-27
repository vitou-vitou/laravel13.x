<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function swipesGiven(): HasMany
    {
        return $this->hasMany(Swipe::class, 'swiper_id');
    }

    public function swipesReceived(): HasMany
    {
        return $this->hasMany(Swipe::class, 'swiped_id');
    }

    public function allMatches()
    {
        return DatingMatch::where('user_one_id', $this->id)
            ->orWhere('user_two_id', $this->id)
            ->with(['userOne.profile', 'userTwo.profile']);
    }

    public function getOtherUser(DatingMatch $match): User
    {
        return $match->user_one_id === $this->id ? $match->userTwo : $match->userOne;
    }
}
