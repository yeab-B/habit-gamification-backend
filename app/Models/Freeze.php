<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Freeze extends Model
{

    protected $fillable = [

        'sender_id',
        'receiver_id',
        'challenge_id',
        'coins_spent',
        'status',
        'used_at'

    ];


    // User who sends freeze
    public function sender()
    {
        return $this->belongsTo(
            User::class,
            'sender_id'
        );
    }


    // User who receives freeze
    public function receiver()
    {
        return $this->belongsTo(
            User::class,
            'receiver_id'
        );
    }


    // Related challenge
    public function challenge()
    {
        return $this->belongsTo(
            Challenge::class
        );
    }

}