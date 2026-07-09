<?php

namespace App\Events;

use App\Models\TaskCompletion;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly TaskCompletion $completion)
    {
    }
}
