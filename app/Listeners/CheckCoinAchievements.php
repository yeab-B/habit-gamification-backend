<?php

namespace App\Listeners;

use App\Events\CoinsEarned;
use App\Services\AchievementService;

class CheckCoinAchievements
{
    public function __construct(private readonly AchievementService $achievementService)
    {
    }

    public function handle(CoinsEarned $event): void
    {
        if ($event->transaction->source === 'achievement') {
            return;
        }

        $this->achievementService->checkAchievements($event->transaction->user, 'coins_earned');
    }
}
