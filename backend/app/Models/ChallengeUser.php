<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallengeUser extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'challenge_id',
        'user_id',
        'status',
        'joined_at'
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
        ];
    }


    // Related challenge
    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }


    // Related user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}