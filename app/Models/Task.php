<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'points',
        'difficulty',
        'frequency'
    ];


    // Task owner
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    // Task category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }


    // Completion history
    public function completions()
    {
        return $this->hasMany(TaskCompletion::class);
    }

}