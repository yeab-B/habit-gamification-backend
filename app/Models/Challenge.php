<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'duration',
        'start_date',
        'end_date',
        'visibility',
        'status'
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    // Challenge creator
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    // Categories included in challenge
    public function categories()
    {
        return $this->belongsToMany(
            Category::class,
            'challenge_categories'
        );
    }


    // Users who joined challenge
    public function participants()
    {
        return $this->belongsToMany(
            User::class,
            'challenge_users'
        );
    }


    // Completed tasks
    public function completions()
    {
        return $this->hasMany(TaskCompletion::class);
    }


    // Daily check-ins
    public function checkins()
    {
        return $this->hasMany(DailyCheckin::class);
    }


    // Streak tracking
    public function streaks()
    {
        return $this->hasMany(Streak::class);
    }

}