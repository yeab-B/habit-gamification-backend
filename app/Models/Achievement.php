<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Achievement extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'category',
        'condition_type',
        'condition_value',
        'reward_coins',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'condition_value' => 'integer',
            'reward_coins' => 'integer',
            'is_active' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    public function userAchievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_achievements'
        )
        ->withPivot([
            'earned_at',
            'reward_coins',
        ]);
    }
}
