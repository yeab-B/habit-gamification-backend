<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;

#[Fillable([
    'name',
    'email',
    'password',
    'google_id',
    'avatar',
    'provider'
])]
#[Hidden([
    'password',
    'remember_token'
])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasUuids, Notifiable, SoftDeletes;


    /**
     * The primary key is not incrementing.
     *
     * @var bool
     */
    public $incrementing = false;


    /**
     * The primary key type.
     *
     * @var string
     */
    protected $keyType = 'string';


    /**
     * User created categories
     */
    public function categories()
    {
        return $this->hasMany(Category::class);
    }


    /**
     * User created tasks
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }


    /**
     * Challenges created by user
     */
    public function challenges()
    {
        return $this->hasMany(Challenge::class);
    }


    /**
     * Tasks completed by user
     */
    public function taskCompletions()
    {
        return $this->hasMany(TaskCompletion::class);
    }


    /**
     * Daily challenge check-ins
     */
    public function dailyCheckins()
    {
        return $this->hasMany(DailyCheckin::class);
    }


    /**
     * User streak records
     */
    public function streaks()
    {
        return $this->hasMany(Streak::class);
    }


    /**
     * Coin earning/loss history
     */
    public function coinTransactions()
    {
        return $this->hasMany(CoinTransaction::class);
    }


    /**
     * Friend requests sent by user
     */
    public function friendshipsSent()
    {
        return $this->hasMany(Friendship::class, 'user_id');
    }


    /**
     * Friend requests received by user
     */
    public function friendshipsReceived()
    {
        return $this->hasMany(Friendship::class, 'friend_id');
    }


    /**
     * Accepted friends
     */
    public function friends()
    {
        return $this->belongsToMany(
            User::class,
            'friendships',
            'user_id',
            'friend_id'
        )
        ->wherePivot('status', 'accepted');
    }


    /**
     * Friend challenges created by user
     */
    public function friendChallengesCreated()
    {
        return $this->hasMany(
            FriendChallenge::class,
            'challenger_id'
        );
    }


    /**
     * Friend challenges received
     */
    public function friendChallengesReceived()
    {
        return $this->hasMany(
            FriendChallenge::class,
            'opponent_id'
        );
    }


    /**
     * Promises created by user
     */
    public function promises()
    {
        return $this->hasMany(Promise::class);
    }


    /**
     * Freezes sent by user
     */
    public function freezesSent()
    {
        return $this->hasMany(Freeze::class, 'sender_id');
    }


    /**
     * Freezes received by user
     */
    public function freezesReceived()
    {
        return $this->hasMany(Freeze::class, 'receiver_id');
    }


    /**
     * Achievements unlocked by user
     */
    public function achievements()
    {
        return $this->belongsToMany(
            Achievement::class,
            'user_achievements'
        )
        ->withPivot('earned_at');
    }


    /**
     * User achievement history
     */
    public function userAchievements()
    {
        return $this->hasMany(UserAchievement::class);
    }


    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'deleted_at' => 'datetime',
        ];
    }
}