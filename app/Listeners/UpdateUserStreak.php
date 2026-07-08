<?php

namespace App\Listeners;

use App\Events\TaskCompleted;
use App\Services\StreakService;

class UpdateUserStreak
{
    public function __construct(private readonly StreakService $streakService)
    {
    }

    public function handle(TaskCompleted $event): void
    {
        $this->streakService->updateStreak(
            $event->completion->user,
            $event->completion->completion_date
        );
    }
}
