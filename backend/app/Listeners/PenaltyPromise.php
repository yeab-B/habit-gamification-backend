<?php

namespace App\Listeners;

use App\Events\PromiseBroken;
use App\Services\CoinService;
use RuntimeException;

class PenaltyPromise
{
    public function __construct(private readonly CoinService $coinService)
    {
    }

    public function handle(PromiseBroken $event): void
    {
        $promise = $event->promise;

        if ($promise->penalty_coins <= 0) {
            return;
        }

        try {
            $this->coinService->penaltyCoins(
                $promise->user,
                'promise_broken',
                $promise->penalty_coins,
                'Promise broken penalty',
                $promise->id
            );
        } catch (RuntimeException) {
            $balance = $this->coinService->getBalance($promise->user);

            if ($balance > 0) {
                $this->coinService->penaltyCoins(
                    $promise->user,
                    'promise_broken',
                    $balance,
                    'Promise broken penalty',
                    $promise->id
                );
            }
        }
    }
}
