<?php

namespace App\Listeners;

use App\Events\FreezeSent;
use App\Events\FriendRequestAccepted;
use App\Events\PromiseFulfilled;
use App\Services\AchievementService;

class CheckSocialAchievements
{
    public function __construct(private readonly AchievementService $achievementService)
    {
    }

    public function handle(FriendRequestAccepted|FreezeSent|PromiseFulfilled $event): void
    {
        if ($event instanceof FriendRequestAccepted) {
            $this->achievementService->checkAchievements($event->friendship->sender, 'friends_count');
            $this->achievementService->checkAchievements($event->friendship->receiver, 'friends_count');

            return;
        }

        if ($event instanceof FreezeSent) {
            $this->achievementService->checkAchievements($event->freeze->sender, 'freezes_sent');

            return;
        }

        $this->achievementService->checkAchievements($event->promise->user, 'promises_fulfilled');
    }
}
