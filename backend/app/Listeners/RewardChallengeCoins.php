<?php

namespace App\Listeners;

use App\Events\ChallengeCompleted;
use App\Models\CoinTransaction;
use App\Services\CoinService;

class RewardChallengeCoins
{
    private const CHALLENGE_COMPLETION_BONUS = 50;

    public function __construct(private readonly CoinService $coinService)
    {
    }

    public function handle(ChallengeCompleted $event): void
    {
        $alreadyRewarded = CoinTransaction::query()
            ->where('user_id', $event->user->id)
            ->where('source', 'challenge_completion')
            ->where('reference_id', $event->challenge->id)
            ->exists();

        if ($alreadyRewarded) {
            return;
        }

        $this->coinService->bonusCoins(
            $event->user,
            'challenge_completion',
            self::CHALLENGE_COMPLETION_BONUS,
            'Challenge completed',
            $event->challenge->id
        );
    }
}
