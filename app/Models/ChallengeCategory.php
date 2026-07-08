<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallengeCategory extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'challenge_id',
        'category_id'
    ];


    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }


    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

}