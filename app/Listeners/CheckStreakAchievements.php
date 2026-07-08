<?php

namespace App\Listeners;

use App\Events\StreakMilestoneReached;
use App\Services\AchievementService;

class CheckStreakAchievements
{
    public function __construct(private readonly AchievementService $achievementService)
    {
    }

    public function handle(StreakMilestoneReached $event): void
    {
        $this->achievementService->checkAchievements($event->streak->user, 'current_streak');
    }
}
