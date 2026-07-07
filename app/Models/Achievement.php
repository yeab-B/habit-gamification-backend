<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{

    protected $fillable = [

        'name',
        'description',
        'icon',
        'type',
        'requirement',
        'reward_coins'

    ];


    // Users who unlocked achievement
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'user_achievements'
        )
        ->withPivot([
            'earned_at'
        ]);
    }

}