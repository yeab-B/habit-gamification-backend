<?php

namespace App\Listeners;

use App\Events\ChallengeCompleted;
use App\Services\AchievementService;

class CheckChallengeAchievements
{
    public function __construct(private readonly AchievementService $achievementService)
    {
    }

    public function handle(ChallengeCompleted $event): void
    {
        $this->achievementService->checkAchievements($event->user, 'challenges_completed');
    }
}
