<?php

namespace App\Finance\Events;

use App\Finance\Models\RewardWallet;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RewardEarned
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly RewardWallet $wallet,
        public readonly float $amount
    ) {
    }
}
