<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promise extends Model
{

    protected $fillable = [

        'user_id',
        'challenge_id',
        'friend_challenge_id',
        'message',
        'promise_date',
        'status',
        'completed_at'

    ];


    // User who created promise
    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }


    // Related challenge
    public function challenge()
    {
        return $this->belongsTo(
            Challenge::class
        );
    }


    // Related friend challenge
    public function friendChallenge()
    {
        return $this->belongsTo(
            FriendChallenge::class
        );
    }

}