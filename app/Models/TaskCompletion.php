<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskCompletion extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'task_id',
        'completed_at',
        'completion_date',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'completion_date' => 'date',
            'deleted_at' => 'datetime',
        ];
    }


    // User who completed task
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    // Completed task
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

}