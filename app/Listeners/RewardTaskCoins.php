<?php

namespace App\Listeners;

use App\Events\TaskCompleted;
use App\Services\CoinService;

class RewardTaskCoins
{
    public function __construct(private readonly CoinService $coinService)
    {
    }

    public function handle(TaskCompleted $event): void
    {
        $completion = $event->completion;

        $this->coinService->earnCoins(
            $completion->user,
            'task_completion',
            $completion->task->points,
            'Task completed',
            $completion->id
        );
    }
}
