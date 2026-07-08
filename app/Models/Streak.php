<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Streak extends Model
{

    protected $fillable = [
        'user_id',
        'challenge_id',
        'current_streak',
        'longest_streak',
        'last_completed_date'
    ];


    // Owner of streak
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    // Related challenge
    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

}