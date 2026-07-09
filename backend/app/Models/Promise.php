<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promise extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'promise_date',
        'validation_date',
        'status',
        'reason',
        'reward_coins',
        'penalty_coins',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'promise_date' => 'date',
            'validation_date' => 'date',
            'reward_coins' => 'integer',
            'penalty_coins' => 'integer',
            'validated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
