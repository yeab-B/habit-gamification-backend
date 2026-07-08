<?php

namespace App\Listeners;

use App\Events\StreakMilestoneReached;
use App\Models\CoinTransaction;
use App\Services\CoinService;

class RewardStreakCoins
{
    private const REWARDS = [
        7 => 20,
        30 => 100,
        100 => 500,
    ];

    public function __construct(private readonly CoinService $coinService)
    {
    }

    public function handle(StreakMilestoneReached $event): void
    {
        $amount = self::REWARDS[$event->milestone] ?? null;

        if ($amount === null) {
            return;
        }

        $alreadyRewarded = CoinTransaction::query()
            ->where('user_id', $event->streak->user_id)
            ->where('source', 'streak_reward')
            ->where('reference_id', $event->streak->id)
            ->where('description', $event->milestone . ' day streak bonus')
            ->exists();

        if ($alreadyRewarded) {
            return;
        }

        $this->coinService->bonusCoins(
            $event->streak->user,
            'streak_reward',
            $amount,
            $event->milestone . ' day streak bonus',
            $event->streak->id
        );
    }
}
