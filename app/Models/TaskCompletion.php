<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskCompletion extends Model
{

    protected $fillable = [
        'user_id',
        'task_id',
        'challenge_id',
        'completed_date',
        'points_earned'
    ];


    // User who completed task
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    // Completed task
    public function task()
    {
        return $this->belongsTo(Task::class);
    }


    // Related challenge
    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

}