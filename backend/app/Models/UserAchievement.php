<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAchievement extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'achievement_id',
        'earned_at',
        'reward_coins',
    ];

    protected function casts(): array
    {
        return [
            'earned_at' => 'datetime',
            'reward_coins' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(
            Achievement::class
        );
    }
}
