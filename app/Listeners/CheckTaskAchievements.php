<?php

namespace App\Listeners;

use App\Events\TaskCompleted;
use App\Services\AchievementService;

class CheckTaskAchievements
{
    public function __construct(private readonly AchievementService $achievementService)
    {
    }

    public function handle(TaskCompleted $event): void
    {
        $this->achievementService->checkAchievements($event->completion->user, 'tasks_completed');
    }
}
