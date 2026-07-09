<?php

namespace App\Listeners;

use App\Events\FreezeSent;
use App\Services\CoinService;

class DeductFreezeCoins
{
    public function __construct(private readonly CoinService $coinService)
    {
    }

    public function handle(FreezeSent $event): void
    {
        $freeze = $event->freeze;

        $this->coinService->spendCoins(
            $freeze->sender,
            'freeze',
            $freeze->cost,
            'Freeze sent',
            $freeze->id
        );
    }
}
