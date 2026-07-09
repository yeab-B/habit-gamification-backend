<?php

namespace App\Listeners;

use App\Events\PromiseBroken;
use App\Events\PromiseFulfilled;
use App\Events\TaskCompleted;
use App\Services\StreakService;

class UpdateUserStreak
{
    public function __construct(private readonly StreakService $streakService)
    {
    }

    public function handle(TaskCompleted|PromiseFulfilled|PromiseBroken $event): void
    {
        if ($event instanceof PromiseFulfilled) {
            $this->streakService->protectPromiseGap(
                $event->promise->user,
                $event->promise->promise_date,
                $event->promise->validation_date
            );

            return;
        }

        if ($event instanceof PromiseBroken) {
            $this->streakService->resetStreak($event->promise->user);

            return;
        }

        $this->streakService->updateStreak(
            $event->completion->user,
            $event->completion->completion_date
        );
    }
}
