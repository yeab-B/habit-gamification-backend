<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoinTransaction extends Model
{

    protected $fillable = [
        'user_id',
        'challenge_id',
        'task_id',
        'type',
        'amount',
        'description'
    ];


    // Transaction owner
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    // Related challenge
    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }


    // Related task
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

}