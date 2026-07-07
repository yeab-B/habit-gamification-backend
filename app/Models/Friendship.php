<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Friendship extends Model
{

    protected $fillable = [
        'user_id',
        'friend_id',
        'status',
        'accepted_at'
    ];


    // User who sent request
    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }


    // User who received request
    public function friend()
    {
        return $this->belongsTo(
            User::class,
            'friend_id'
        );
    }

}