<?php

namespace App\Listeners;

use App\Events\PromiseFulfilled;
use App\Services\CoinService;

class RewardPromise
{
    public function __construct(private readonly CoinService $coinService)
    {
    }

    public function handle(PromiseFulfilled $event): void
    {
        $promise = $event->promise;

        if ($promise->reward_coins <= 0) {
            return;
        }

        $this->coinService->bonusCoins(
            $promise->user,
            'promise_fulfilled',
            $promise->reward_coins,
            'Promise fulfilled bonus',
            $promise->id
        );
    }
}
