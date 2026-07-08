<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChallengeUser extends Model
{

    protected $fillable = [
        'challenge_id',
        'user_id',
        'status',
        'joined_at'
    ];


    // Related challenge
    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }


    // Related user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}