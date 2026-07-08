<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'points',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'is_active' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }


    // Task owner
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    // Task category
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }


    // Completion history
    public function completions(): HasMany
    {
        return $this->hasMany(TaskCompletion::class);
    }

}