<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Challenge extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'duration_days',
        'start_date',
        'end_date',
        'status'
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'deleted_at' => 'datetime',
        ];
    }

    // Challenge creator
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    // Categories included in challenge
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'challenge_categories',
            'challenge_id',
            'category_id'
        );
    }


    // Users who joined challenge
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'challenge_users',
            'challenge_id',
            'user_id'
        )->withPivot(['joined_at', 'status']);
    }

}