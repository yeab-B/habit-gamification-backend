<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChallengeCategory extends Model
{

    protected $fillable = [
        'challenge_id',
        'category_id'
    ];


    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

}