<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FriendChallenge extends Model
{

    protected $fillable = [

        'challenge_id',
        'challenger_id',
        'opponent_id',
        'status',
        'winner_id',
        'stake_coins',
        'completed_at'

    ];


    // Related challenge
    public function challenge()
    {
        return $this->belongsTo(
            Challenge::class
        );
    }


    // User who created challenge
    public function challenger()
    {
        return $this->belongsTo(
            User::class,
            'challenger_id'
        );
    }


    // User who receives challenge
    public function opponent()
    {
        return $this->belongsTo(
            User::class,
            'opponent_id'
        );
    }


    // Winner
    public function winner()
    {
        return $this->belongsTo(
            User::class,
            'winner_id'
        );
    }

}