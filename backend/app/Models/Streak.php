<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Streak extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'current_streak',
        'longest_streak',
        'last_completed_date',
    ];

    protected function casts(): array
    {
        return [
            'current_streak' => 'integer',
            'longest_streak' => 'integer',
            'last_completed_date' => 'date',
            'deleted_at' => 'datetime',
        ];
    }

    // Owner of streak
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
