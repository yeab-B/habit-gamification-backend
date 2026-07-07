<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAchievement extends Model
{

    protected $fillable = [

        'user_id',
        'achievement_id',
        'earned_at'

    ];


    // User who earned achievement
    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }


    // Achievement information
    public function achievement()
    {
        return $this->belongsTo(
            Achievement::class
        );
    }

}