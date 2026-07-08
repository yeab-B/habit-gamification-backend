<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyCheckin extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'date',
        'tasks_completed',
        'total_points',
        'is_completed',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'tasks_completed' => 'integer',
            'total_points' => 'integer',
            'is_completed' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }


    // User who checked in
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}