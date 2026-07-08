<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyCheckin extends Model
{

    protected $fillable = [
        'user_id',
        'challenge_id',
        'date',
        'completed_tasks',
        'coins_earned',
        'status'
    ];


    // User who checked in
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