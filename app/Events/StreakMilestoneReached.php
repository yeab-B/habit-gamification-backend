<?php

namespace App\Events;

use App\Models\Streak;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StreakMilestoneReached
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Streak $streak,
        public readonly int $milestone
    ) {
    }
}
